<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Projects_model extends CRM_Model
{
    private $project_settings;

    public function __construct()
    {
        parent::__construct();

        $project_settings       = array(
            'available_features',
            'view_tasks',
            'create_tasks',
            'edit_tasks',
            'comment_on_tasks',
            'view_task_comments',
            'view_task_attachments',
            'view_task_checklist_items',
            'upload_on_tasks',
            'view_task_total_logged_time',
            'view_finance_overview',
            'upload_files',
            'open_discussions',
            'view_milestones',
            'view_gantt',
            'view_timesheets',
            'view_activity_log',
            'view_team_members',
            'hide_tasks_on_main_tasks_table',
        );

        $this->project_settings = do_action('project_settings', $project_settings);
    }

    public function get_project_statuses()
    {
        $statuses = do_action('before_get_project_statuses', array(
            array(
                'id'=>1,
                'color'=>'#989898',
                'name'=>_l('project_status_1'),
                'order'=>1,
                'filter_default'=>true,
                ),
            array(
                'id'=>2,
                'color'=>'#03a9f4',
                'name'=>_l('project_status_2'),
                'order'=>2,
                'filter_default'=>true,
                ),
            array(
                'id'=>3,
                'color'=>'#ff6f00',
                'name'=>_l('project_status_3'),
                'order'=>3,
                'filter_default'=>true,
                ),
            array(
                'id'=>4,
                'color'=>'#84c529',
                'name'=>_l('project_status_4'),
                'order'=>100,
                'filter_default'=>false,
                ),
            array(
                'id'=>5,
                'color'=>'#989898',
                'name'=>_l('project_status_5'),
                'order'=>4,
                'filter_default'=>false,
                ),
            ));

        usort($statuses, function ($a, $b) {
            return $a['order'] - $b['order'];
        });

        return $statuses;
    }

    public function get_distinct_tasks_timesheets_staff($project_id)
    {
        return $this->db->query('SELECT DISTINCT staff_id FROM tbltaskstimers LEFT JOIN tblstafftasks ON tblstafftasks.id = tbltaskstimers.task_id WHERE rel_type="project" AND rel_id=' . $project_id)->result_array();
    }

    public function get_most_used_billing_type()
    {
        return $this->db->query("SELECT billing_type, COUNT(*) AS total_usage
                FROM tblprojects
                GROUP BY billing_type
                ORDER BY total_usage DESC
                LIMIT 1")->row();
    }

    public function timers_started_for_project($project_id, $where = array(), $task_timers_where = array())
    {
        $this->db->where($where);
        $this->db->where('end_time IS NULL');
        $this->db->where('tblstafftasks.rel_id', $project_id);
        $this->db->where('tblstafftasks.rel_type', 'project');
        $this->db->join('tblstafftasks', 'tblstafftasks.id=tbltaskstimers.task_id');
        $total = $this->db->count_all_results('tbltaskstimers');

        return $total > 0 ? true : false;
    }

    public function pin_action($id)
    {
        if (total_rows('tblpinnedprojects', array(
            'staff_id' => get_staff_user_id(),
            'project_id' => $id,
        )) == 0) {
            $this->db->insert('tblpinnedprojects', array(
                'staff_id' => get_staff_user_id(),
                'project_id' => $id,
            ));

            return true;
        } else {
            $this->db->where('project_id', $id);
            $this->db->where('staff_id', get_staff_user_id());
            $this->db->delete('tblpinnedprojects');

            return true;
        }
    }

    public function get_currency($id)
    {
        $this->load->model('currencies_model');
        $customer_currency = $this->clients_model->get_customer_default_currency(get_client_id_by_project_id($id));
        if ($customer_currency != 0) {
            $currency = $this->currencies_model->get($customer_currency);
        } else {
            $currency = $this->currencies_model->get_base_currency();
        }

        return $currency;
    }

    public function calc_progress($id)
    {
        $this->db->select('progress_from_tasks,progress,status');
        $this->db->where('id', $id);
        $project =  $this->db->get('tblprojects')->row();

        if ($project->status == 4) {
            return 100;
        }

        if ($project->progress_from_tasks == 1) {
            return $this->calc_progress_by_tasks($id);
        } else {
            return $project->progress;
        }
    }

    public function calc_progress_by_tasks($id)
    {
        $total_project_tasks  = total_rows('tblstafftasks', array(
            'rel_type' => 'project',
            'rel_id' => $id,
        ));
        $total_finished_tasks = total_rows('tblstafftasks', array(
            'rel_type' => 'project',
            'rel_id' => $id,
            'status' => 5,
        ));
        $percent              = 0;
        if ($total_finished_tasks >= floatval($total_project_tasks)) {
            $percent = 100;
        } else {
            if ($total_project_tasks !== 0) {
                $percent = number_format(($total_finished_tasks * 100) / $total_project_tasks, 2);
            }
        }

        return $percent;
    }

    public function get_last_project_settings()
    {
        $this->db->select('id');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $last_project = $this->db->get('tblprojects')->row();
        if ($last_project) {
            return $this->get_project_settings($last_project->id);
        }

        return array();
    }

    public function get_settings()
    {
        return $this->project_settings;
    }

    public function get($id = '', $where = array())
    {
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            $project = $this->db->get('tblprojects')->row();
            if ($project) {
                $project->shared_vault_entries = $this->clients_model->get_vault_entries($project->clientid, array('share_in_projects'=>1));
                $settings          = $this->get_project_settings($id);

                // SYNC NEW TABS
                $tabs = get_project_tabs_admin(null);
                $tabs_flatten = array();
                $settings_available_features = array();

                $available_features_index = false;
                foreach ($settings as $key => $setting) {
                    if ($setting['name'] == 'available_features') {
                        $available_features_index = $key;
                        $available_features = unserialize($setting['value']);
                        if (is_array($available_features)) {
                            foreach ($available_features as $name => $avf) {
                                $settings_available_features[] = $name;
                            }
                        }
                    }
                }
                foreach ($tabs as $tab) {
                    if (isset($tab['dropdown'])) {
                        foreach ($tab['dropdown'] as $d) {
                            $tabs_flatten[] = $d['name'];
                        }
                    } else {
                        $tabs_flatten[] = $tab['name'];
                    }
                }
                if (count($settings_available_features) != $tabs_flatten) {
                    foreach ($tabs_flatten as $tab) {
                        if (!in_array($tab, $settings_available_features)) {
                            if ($available_features_index) {
                                $current_available_features_settings = $settings[$available_features_index];
                                $tmp = unserialize($current_available_features_settings['value']);
                                $tmp[$tab] = 1;
                                $this->db->where('id', $current_available_features_settings['id']);
                                $this->db->update('tblprojectsettings', array('value'=>serialize($tmp)));
                            }
                        }
                    }
                }
                $project->settings = new StdClass();
                foreach ($settings as $setting) {
                    $project->settings->{$setting['name']} = $setting['value'];
                }

                // In case any settings missing add them and set default 0 to prevent errors
                foreach ($this->project_settings as $setting) {
                    if (!isset($project->settings->{$setting})) {
                        $this->db->insert('tblprojectsettings', array(
                            'project_id' => $id,
                            'name' => $setting,
                            'value' => 0,
                        ));
                        $project->settings->{$setting} = 0;
                    }
                }
                $project->client_data = new StdClass();
                $project->client_data = $this->clients_model->get($project->clientid);

                return do_action('project_get', $project);
            }

            return null;
        }

        $this->db->select('*,'.get_sql_select_client_company());
        $this->db->join('tblclients', 'tblclients.userid=tblprojects.clientid');
        $this->db->order_by('id', 'desc');

        return $this->db->get('tblprojects')->result_array();
    }

    public function calculate_total_by_project_hourly_rate($seconds, $hourly_rate)
    {
        $hours       = seconds_to_time_format($seconds);
        $decimal     = sec2qty($seconds);
        $total_money = 0;
        $total_money += ($decimal * $hourly_rate);

        return array(
            'hours' => $hours,
            'total_money' => $total_money,
        );
    }

    public function calculate_total_by_task_hourly_rate($tasks)
    {
        $total_money    = 0;
        $_total_seconds = 0;

        foreach ($tasks as $task) {
            $seconds = $task['total_logged_time'];
            $_total_seconds += $seconds;
            $total_money += sec2qty($seconds) * $task['hourly_rate'];
        }

        return array(
            'total_money' => $total_money,
            'total_seconds' => $_total_seconds,
        );
    }

    public function get_tasks($id, $where = array(), $apply_restrictions = false, $count = false)
    {
        $has_permission = has_permission('tasks', '', 'view');
        $show_all_tasks_for_project_member = get_option('show_all_tasks_for_project_member');

        if (is_client_logged_in()) {
            $this->db->where('visible_to_client', 1);
        }

        $select = implode(', ', prefixed_table_fields_array('tblstafftasks')).',tblmilestones.name as milestone_name,
        (SELECT SUM(CASE
            WHEN end_time is NULL THEN '.time().'-start_time
            ELSE end_time-start_time
            END) FROM tbltaskstimers WHERE task_id=tblstafftasks.id) as total_logged_time,
           '.get_sql_select_task_assignees_ids().' as assignees_ids
        ';

        if(!is_client_logged_in() && is_staff_logged_in()) {
            $select .= ',(SELECT staffid FROM tblstafftaskassignees WHERE taskid=tblstafftasks.id AND staffid='.get_staff_user_id().') as current_user_is_assigned';
        }
        $this->db->select($select);

        $this->db->join('tblmilestones', 'tblmilestones.id = tblstafftasks.milestone', 'left');
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'project');
        if ($apply_restrictions == true) {
            if (!is_client_logged_in() && !$has_permission && $show_all_tasks_for_project_member == 0) {
                $this->db->where('(
                    tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid='.get_staff_user_id().')
                    OR tblstafftasks.id IN(SELECT taskid FROM tblstafftasksfollowers WHERE staffid='.get_staff_user_id().')
                    OR is_public = 1
                    OR (addedfrom ='.get_staff_user_id().' AND is_added_from_contact = 0)
                    )');
            }
        }
        $this->db->order_by('milestone_order', 'asc');
        $this->db->where($where);

        if ($count == false) {
            $tasks = $this->db->get('tblstafftasks')->result_array();
        } else {
            $tasks = $this->db->count_all_results('tblstafftasks');
        }

        return $tasks;
    }

    public function do_milestones_kanban_query($milestone_id, $project_id, $page = 1, $where = array(), $count = false)
    {
        $where['milestone'] = $milestone_id;

        if ($count == false) {
            if ($page > 1) {
                $page--;
                $position = ($page * get_option('tasks_kanban_limit'));
                $this->db->limit(get_option('tasks_kanban_limit'), $position);
            } else {
                $this->db->limit(get_option('tasks_kanban_limit'));
            }
        }

        return $this->get_tasks($project_id, $where, true, $count);
    }

    public function get_files($project_id)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('project_id', $project_id);

        return $this->db->get('tblprojectfiles')->result_array();
    }

    public function get_file($id, $project_id = false)
    {
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        $this->db->where('id', $id);
        $file = $this->db->get('tblprojectfiles')->row();

        if ($file && $project_id) {
            if ($file->project_id != $project_id) {
                return false;
            }
        }

        return $file;
    }

    public function update_file_data($data)
    {
        $this->db->where('id', $data['id']);
        unset($data['id']);
        $this->db->update('tblprojectfiles', $data);
    }

    public function change_file_visibility($id, $visible)
    {
        $this->db->where('id', $id);
        $this->db->update('tblprojectfiles', array(
            'visible_to_customer' => $visible,
        ));
    }

    public function change_activity_visibility($id, $visible)
    {
        $this->db->where('id', $id);
        $this->db->update('tblprojectactivity', array(
            'visible_to_customer' => $visible,
        ));
    }

    public function remove_file($id)
    {
        $id = do_action('before_remove_project_file', $id);

        $this->db->where('id', $id);
        $file = $this->db->get('tblprojectfiles')->row();
        if ($file) {
            if (empty($file->external)) {
                $path = get_upload_path_by_type('project') . $file->project_id . '/';
                $fullPath =$path.$file->file_name;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                    $fname = pathinfo($fullPath, PATHINFO_FILENAME);
                    $fext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $thumbPath = $path.$fname.'_thumb.'.$fext;

                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                    }
                }
            }

            $this->db->where('id', $id);
            $this->db->delete('tblprojectfiles');
            $this->log_activity($file->project_id, 'project_activity_project_file_removed', $file->file_name, $file->visible_to_customer);
            // Delete discussion comments
            $this->_delete_discussion_comments($id, 'file');

            if (is_dir(get_upload_path_by_type('project') . $file->project_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('project') . $file->project_id);
                if (count($other_attachments) == 0) {
                    delete_dir(get_upload_path_by_type('project') . $file->project_id);
                }
            }

            return true;
        }

        return false;
    }

    public function get_project_overview_weekly_chart_data($id, $type = 'this_week')
    {
        $billing_type = get_project_billing_type($id);
        $chart = array();

        $has_permission_create = has_permission('projects', '', 'create');
        // If don't have permission for projects create show only bileld time
        if (!$has_permission_create) {
            $timesheets_type = 'total_logged_time_only';
        } else {
            if ($billing_type == 2 || $billing_type == 3) {
                $timesheets_type = 'billable_unbilled';
            } else {
                $timesheets_type = 'total_logged_time_only';
            }
        }

        $chart['data'] = array();
        $chart['data']['labels'] = array();
        $chart['data']['datasets'] = array();

        $chart['data']['datasets'][] = array(
            'label'=>($timesheets_type == 'billable_unbilled' ? str_replace(':', '', _l('project_overview_billable_hours')) : str_replace(':', '', _l('project_overview_logged_hours'))),
            'data'=>array(),
            'backgroundColor'=>array(),
            'borderColor'=>array(),
            'borderWidth'=>1,
            );

        if ($timesheets_type == 'billable_unbilled') {
            $chart['data']['datasets'][] = array(
                'label'=>str_replace(':', '', _l('project_overview_unbilled_hours')),
                'data'=>array(),
                'backgroundColor'=>array(),
                'borderColor'=>array(),
                'borderWidth'=>1,
                );
        }

        $temp_weekdays_data = array();
        $weeks = array();
        $where_time = '';

        if ($type == 'this_month') {
            $beginThisMonth = date('Y-m-01');
            $endThisMonth   = date('Y-m-t 23:59:59');

            $weeks_split_start = date('Y-m-d', strtotime($beginThisMonth));
            $weeks_split_end = date('Y-m-d', strtotime($endThisMonth));

            $where_time = 'start_time BETWEEN ' . strtotime($beginThisMonth) . ' AND ' . strtotime($endThisMonth);
        } elseif ($type == 'last_month') {
            $beginLastMonth = date('Y-m-01', strtotime('-1 MONTH'));
            $endLastMonth   = date('Y-m-t 23:59:59', strtotime('-1 MONTH'));

            $weeks_split_start = date('Y-m-d', strtotime($beginLastMonth));
            $weeks_split_end = date('Y-m-d', strtotime($endLastMonth));

            $where_time = 'start_time BETWEEN ' . strtotime($beginLastMonth) . ' AND ' . strtotime($endLastMonth);
        } elseif ($type == 'last_week') {
            $beginLastWeek = date('Y-m-d', strtotime('monday last week'));
            $endLastWeek   = date('Y-m-d 23:59:59', strtotime('sunday last week'));
            $where_time = 'start_time BETWEEN ' . strtotime($beginLastWeek) . ' AND ' . strtotime($endLastWeek);
        } else {
            $beginThisWeek = date('Y-m-d', strtotime('monday this week'));
            $endThisWeek   = date('Y-m-d 23:59:59', strtotime('sunday this week'));
            $where_time = 'start_time BETWEEN ' . strtotime($beginThisWeek) . ' AND ' . strtotime($endThisWeek);
        }

        if ($type == 'this_week' || $type == 'last_week') {
            foreach (get_weekdays() as $day) {
                array_push($chart['data']['labels'], $day);
            }
            $weekDay = date('w', strtotime(date('Y-m-d H:i:s')));
            $i = 0;
            foreach (get_weekdays_original() as $day) {
                if ($weekDay != "0") {
                    $chart['data']['labels'][$i] = date('d', strtotime($day. ' ' . str_replace('_', ' ', $type))). ' - ' .$chart['data']['labels'][$i];
                } else {
                    if ($type == 'this_week') {
                        $strtotime = 'last '.$day;
                        if ($day == 'Sunday') {
                            $strtotime = 'sunday this week';
                        }
                        $chart['data']['labels'][$i] = date('d', strtotime($strtotime)). ' - ' .$chart['data']['labels'][$i];
                    } else {
                        $strtotime = $day .' last week';
                        $chart['data']['labels'][$i] = date('d', strtotime($strtotime)). ' - ' .$chart['data']['labels'][$i];
                    }
                }
                $i++;
            }
        } elseif ($type == 'this_month' || $type == 'last_month') {
            $weeks_split_start = new DateTime($weeks_split_start);
            $weeks_split_end = new DateTime($weeks_split_end);
            $weeks = get_weekdays_between_dates($weeks_split_start, $weeks_split_end);
            $total_weeks = count($weeks);
            for ($i = 1; $i<=$total_weeks; $i++) {
                array_push($chart['data']['labels'], split_weeks_chart_label($weeks, $i));
            }
        }

        $loop_break = ($timesheets_type == 'billable_unbilled') ? 2 : 1;

        for ($i=0; $i<$loop_break; $i++) {
            $temp_weekdays_data = array();
            // Store the weeks in new variable for each loop to prevent duplicating
            $tmp_weeks = $weeks;


            $color = '3, 169, 244';

            $where = 'task_id IN (SELECT id FROM tblstafftasks WHERE rel_type = "project" AND rel_id = "'.$id.'"';

            if ($timesheets_type != 'total_logged_time_only') {
                $where .= ' AND billable=1';
                if ($i == 1) {
                    $color = '252, 45, 66';
                    $where .= ' AND billed = 0';
                }
            }

            $where .= ')';
            $this->db->where($where_time);
            $this->db->where($where);
            if (!$has_permission_create) {
                $this->db->where('staff_id', get_staff_user_id());
            }
            $timesheets = $this->db->get('tbltaskstimers')->result_array();

            foreach ($timesheets as $t) {
                $total_logged_time = 0;
                if ($t['end_time'] == null) {
                    $total_logged_time = time() - $t['start_time'];
                } else {
                    $total_logged_time = $t['end_time'] - $t['start_time'];
                }

                if ($type == 'this_week' || $type == 'last_week') {
                    $weekday = date('N', $t['start_time']);
                    if (!isset($temp_weekdays_data[$weekday])) {
                        $temp_weekdays_data[$weekday] = 0;
                    }
                    $temp_weekdays_data[$weekday] += $total_logged_time;
                } else {
                    // months - this and last
                    $w = 1;
                    foreach ($tmp_weeks as $week) {
                        $start_time_date = strftime('%Y-%m-%d', $t['start_time']);
                        if (!isset($tmp_weeks[$w]['total'])) {
                            $tmp_weeks[$w]['total'] = 0;
                        }
                        if (in_array($start_time_date, $week)) {
                            $tmp_weeks[$w]['total'] += $total_logged_time;
                        }
                        $w++;
                    }
                }
            }

            if ($type == 'this_week' || $type == 'last_week') {
                ksort($temp_weekdays_data);
                for ($w = 1; $w<=7; $w++) {
                    $total_logged_time = 0;
                    if (isset($temp_weekdays_data[$w])) {
                        $total_logged_time = $temp_weekdays_data[$w];
                    }
                    array_push($chart['data']['datasets'][$i]['data'], sec2qty($total_logged_time));
                    array_push($chart['data']['datasets'][$i]['backgroundColor'], 'rgba('.$color.',0.8)');
                    array_push($chart['data']['datasets'][$i]['borderColor'], 'rgba('.$color.',1)');
                }
            } else {
                // loop over $tmp_weeks because the unbilled is shown twice because we auto increment twice
                // months - this and last
                foreach ($tmp_weeks as $week) {
                    $total = 0;
                    if (isset($week['total'])) {
                        $total = $week['total'];
                    }
                    $total_logged_time = $total;
                    array_push($chart['data']['datasets'][$i]['data'], sec2qty($total_logged_time));
                    array_push($chart['data']['datasets'][$i]['backgroundColor'], 'rgba('.$color.',0.8)');
                    array_push($chart['data']['datasets'][$i]['borderColor'], 'rgba('.$color.',1)');
                }
            }
        }

        return $chart;
    }

    public function get_gantt_data($project_id, $type = 'milestones', $taskStatus = null)
    {
        $type_data = array();
        if ($type == 'milestones') {
            $type_data[] = array(
                'name' => _l('milestones_uncategorized'),
                'id' => 0,
            );
            $_milestones = $this->get_milestones($project_id);
            foreach ($_milestones as $m) {
                $type_data[] = $m;
            }
        } elseif ($type == 'members') {
            $type_data[] = array(
                'name' => _l('task_list_not_assigned'),
                'staff_id' => 0,
            );
            $_members    = $this->get_project_members($project_id);
            foreach ($_members as $m) {
                $type_data[] = $m;
            }
        } else {
            if (!$taskStatus) {
                $statuses = $this->tasks_model->get_statuses();
                foreach ($statuses as $status) {
                    $type_data[] = $status['id'];
                }
            } else {
                $type_data[] = $taskStatus;
            }
        }

        $gantt_data     = array();
        $has_permission = has_permission('tasks', '', 'view');
        foreach ($type_data as $data) {
            if ($type == 'milestones') {
                $tasks = $this->get_tasks($project_id, 'milestone='.$data['id'] . ($taskStatus ? ' AND tblstafftasks.status='.$taskStatus : ''), true);
                $name  = $data['name'];
            } elseif ($type == 'members') {
                if ($data['staff_id'] != 0) {
                    $tasks = $this->get_tasks($project_id, 'tblstafftasks.id IN (SELECT taskid FROM tblstafftaskassignees WHERE staffid=' . $data['staff_id'] . ')' . ($taskStatus ? ' AND tblstafftasks.status='.$taskStatus : ''), true);
                    $name  = get_staff_full_name($data['staff_id']);
                } else {
                    $tasks = $this->get_tasks($project_id, 'tblstafftasks.id NOT IN (SELECT taskid FROM tblstafftaskassignees)' . ($taskStatus ? ' AND tblstafftasks.status='.$taskStatus : ''), true);
                    $name  = $data['name'];
                }
            } else {
                $tasks = $this->get_tasks($project_id, array(
                    'status' => $data,
                ), true);

                $name  = format_task_status($data, false, true);
            }

            if (count($tasks) > 0) {
                $data           = array();
                $data['values'] = array();
                $values         = array();
                $data['desc']   = $tasks[0]['name'];
                $data['name']   = $name;
                $class          = '';
                if ($tasks[0]['status'] == 5) {
                    $class = 'line-throught';
                }

                $values['from']  = strftime('%Y/%m/%d', strtotime($tasks[0]['startdate']));
                $values['to']    = strftime('%Y/%m/%d', strtotime($tasks[0]['duedate']));
                $values['desc']  = $tasks[0]['name'] . ' - ' . _l('task_total_logged_time') . ' ' . seconds_to_time_format($tasks[0]['total_logged_time']);
                $values['label'] = $tasks[0]['name'];
                if ($tasks[0]['duedate'] && date('Y-m-d') > $tasks[0]['duedate'] && $tasks[0]['status'] != 5) {
                    $values['customClass'] = 'ganttRed';
                } elseif ($tasks[0]['status'] == 5) {
                    $values['label']       = ' <i class="fa fa-check"></i> ' . $values['label'];
                    $values['customClass'] = 'ganttGreen';
                }
                $values['dataObj'] = array(
                    'task_id' => $tasks[0]['id'],
                );
                $data['values'][]  = $values;
                $gantt_data[]      = $data;
                unset($tasks[0]);
                foreach ($tasks as $task) {
                    $data           = array();
                    $data['values'] = array();
                    $values         = array();
                    $class          = '';
                    if ($task['status'] == 5) {
                        $class = 'line-throught';
                    }
                    $data['desc'] = $task['name'];
                    $data['name'] = '';

                    $values['from']  = strftime('%Y/%m/%d', strtotime($task['startdate']));
                    $values['to']    = strftime('%Y/%m/%d', strtotime($task['duedate']));
                    $values['desc']  = $task['name'] . ' - ' ._l('task_total_logged_time') . ' ' . seconds_to_time_format($task['total_logged_time']);
                    $values['label'] = $task['name'];
                    if ($task['duedate'] && date('Y-m-d') > $task['duedate'] && $task['status'] != 5) {
                        $values['customClass'] = 'ganttRed';
                    } elseif ($task['status'] == 5) {
                        $values['label']       = ' <i class="fa fa-check"></i> ' . $values['label'];
                        $values['customClass'] = 'ganttGreen';
                    }

                    $values['dataObj'] = array(
                        'task_id' => $task['id'],
                    );
                    $data['values'][]  = $values;
                    $gantt_data[]      = $data;
                }
            }
        }

        return $gantt_data;
    }

    public function calc_milestone_logged_time($project_id, $id)
    {
        $total = array();
        $tasks = $this->get_tasks($project_id, array(
            'milestone' => $id,
        ));

        foreach ($tasks as $task) {
            $total[] = $task['total_logged_time'];
        }

        return array_sum($total);
    }

    public function total_logged_time($id)
    {
        $q = $this->db->query('
            SELECT SUM(CASE
                WHEN end_time is NULL THEN '.time().'-start_time
                ELSE end_time-start_time
                END) as total_logged_time
            FROM tbltaskstimers
            WHERE task_id IN (SELECT id FROM tblstafftasks WHERE rel_type="project" AND rel_id='.$id.')')
        ->row();

        return $q->total_logged_time;
    }

    public function get_milestones($project_id)
    {
        $this->db->where('project_id', $project_id);
        $this->db->order_by('milestone_order', 'ASC');
        $milestones = $this->db->get('tblmilestones')->result_array();
        $i          = 0;
        foreach ($milestones as $milestone) {
            $milestones[$i]['total_logged_time'] = $this->calc_milestone_logged_time($project_id, $milestone['id']);
            $i++;
        }

        return $milestones;
    }

    public function add_milestone($data)
    {
        $data['due_date']    = to_sql_date($data['due_date']);
        $data['datecreated'] = date('Y-m-d');
        $data['description'] = nl2br($data['description']);

        if (isset($data['description_visible_to_customer'])) {
            $data['description_visible_to_customer'] = 1;
        } else {
            $data['description_visible_to_customer'] = 0;
        }
        $this->db->insert('tblmilestones', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->db->where('id', $insert_id);
            $milestone = $this->db->get('tblmilestones')->row();
            $project   = $this->get($milestone->project_id);
            if ($project->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->project_id, 'project_activity_created_milestone', $milestone->name, $show_to_customer);
            logActivity('Project Milestone Created [ID:' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_milestone($data, $id)
    {
        $this->db->where('id', $id);
        $milestone           = $this->db->get('tblmilestones')->row();
        $data['due_date']    = to_sql_date($data['due_date']);
        $data['description'] = nl2br($data['description']);

        if (isset($data['description_visible_to_customer'])) {
            $data['description_visible_to_customer'] = 1;
        } else {
            $data['description_visible_to_customer'] = 0;
        }

        $this->db->where('id', $id);
        $this->db->update('tblmilestones', $data);
        if ($this->db->affected_rows() > 0) {
            $project = $this->get($milestone->project_id);
            if ($project->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->project_id, 'project_activity_updated_milestone', $milestone->name, $show_to_customer);
            logActivity('Project Milestone Updated [ID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function update_task_milestone($data)
    {
        $this->db->where('id', $data['task_id']);
        $this->db->update('tblstafftasks', array(
            'milestone' => $data['milestone_id'],
        ));

        foreach ($data['order'] as $order) {

            $this->db->where('id', $order[0]);
            $this->db->update('tblstafftasks', array(
                'milestone_order' => $order[1],
            ));
        }
    }

     public function update_milestones_order($data)
    {
        foreach ($data['order'] as $status) {
            $this->db->where('id', $status[0]);
            $this->db->update('tblmilestones', array(
                'milestone_order' => $status[1]
            ));
        }
    }

    public function update_milestone_color($data)
    {
        $this->db->where('id', $data['milestone_id']);
        $this->db->update('tblmilestones', array(
            'color' => $data['color'],
        ));
    }

    public function delete_milestone($id)
    {
        $this->db->where('id', $id);
        $milestone = $this->db->get('tblmilestones')->row();
        $this->db->where('id', $id);
        $this->db->delete('tblmilestones');
        if ($this->db->affected_rows() > 0) {
            $project = $this->get($milestone->project_id);
            if ($project->settings->view_milestones == 1) {
                $show_to_customer = 1;
            } else {
                $show_to_customer = 0;
            }
            $this->log_activity($milestone->project_id, 'project_activity_deleted_milestone', $milestone->name, $show_to_customer);
            $this->db->where('milestone', $id);
            $this->db->update('tblstafftasks', array(
                'milestone' => 0,
            ));
            logActivity('Project Milestone Deleted [' . $id . ']');

            return true;
        }

        return false;
    }

    public function add($data)
    {
        if (isset($data['notify_project_members_status_change'])) {
            unset($data['notify_project_members_status_change']);
        }
        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }

        $send_project_marked_as_finished_email_to_contacts = false;
        if (isset($data['project_marked_as_finished_email_to_contacts'])) {
            unset($data['project_marked_as_finished_email_to_contacts']);
            $send_project_marked_as_finished_email_to_contacts = true;
        }

        if (isset($data['settings'])) {
            $project_settings = $data['settings'];
            unset($data['settings']);
        }
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }
        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }



        $data['start_date'] = to_sql_date($data['start_date']);

        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            unset($data['deadline']);
        }

        $data['project_created'] = date('Y-m-d');
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
            unset($data['project_members']);
        }
        if ($data['billing_type'] == 1) {
            $data['project_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['project_cost'] = 0;
        } else {
            $data['project_rate_per_hour'] = 0;
            $data['project_cost']          = 0;
        }

        $data['addedfrom'] = get_staff_user_id();

        $data = do_action('before_add_project', $data);

        $tags = '';
        if (isset($data['tags'])) {
            $tags  = $data['tags'];
            unset($data['tags']);
        }

        $this->db->insert('tblprojects', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            handle_tags_save($tags, $insert_id, 'project');

            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            if (isset($project_members)) {
                $_pm['project_members'] = $project_members;
                $this->add_edit_members($_pm, $insert_id);
            }

            $original_settings = $this->get_settings();
            if (isset($project_settings)) {
                $_settings = array();
                $_values = array();
                foreach ($project_settings as $name => $val) {
                    array_push($_settings, $name);
                    $_values[$name] = $val;
                }
                foreach ($original_settings as $setting) {
                    if ($setting != 'available_features') {
                        if (in_array($setting, $_settings)) {
                            $value_setting = 1;
                        } else {
                            $value_setting = 0;
                        }
                    } else {
                        $tabs = get_project_tabs_admin(null);
                        $tab_settings = array();
                        foreach ($_values[$setting] as $tab) {
                            $tab_settings[$tab] = 1;
                        }
                        foreach ($tabs as $tab) {
                            if (!isset($tab['dropdown'])) {
                                if (!in_array($tab['name'], $_values[$setting])) {
                                    $tab_settings[$tab['name']] = 0;
                                }
                            } else {
                                foreach ($tab['dropdown'] as $tab_dropdown) {
                                    if (!in_array($tab_dropdown['name'], $_values[$setting])) {
                                        $tab_settings[$tab_dropdown['name']] = 0;
                                    }
                                }
                            }
                        }
                        $value_setting = serialize($tab_settings);
                    }
                    $this->db->insert('tblprojectsettings', array(
                        'project_id' => $insert_id,
                        'name' => $setting,
                        'value' => $value_setting,
                    ));
                }
            } else {
                foreach ($original_settings as $setting) {
                    $value_setting = 0;
                    $this->db->insert('tblprojectsettings', array(
                        'project_id' => $insert_id,
                        'name' => $setting,
                        'value' => $value_setting,
                    ));
                }
            }
            $this->log_activity($insert_id, 'project_activity_created');

            if ($send_created_email == true) {
                $this->send_project_customer_email($insert_id, 'assigned-to-project');
            }

            if ($send_project_marked_as_finished_email_to_contacts == true) {
                $this->send_project_customer_email($insert_id, 'project-finished-to-customer');
            }

            do_action('after_add_project', $insert_id);
            logActivity('New Project Created [ID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    public function update($data, $id)
    {
        $this->db->select('status');
        $this->db->where('id', $id);
        $old_status = $this->db->get('tblprojects')->row()->status;

        $send_created_email = false;
        if (isset($data['send_created_email'])) {
            unset($data['send_created_email']);
            $send_created_email = true;
        }

        $send_project_marked_as_finished_email_to_contacts = false;
        if (isset($data['project_marked_as_finished_email_to_contacts'])) {
            unset($data['project_marked_as_finished_email_to_contacts']);
            $send_project_marked_as_finished_email_to_contacts = true;
        }

        $original_project = $this->get($id);

        if (isset($data['notify_project_members_status_change'])) {
            $notify_project_members_status_change = true;
            unset($data['notify_project_members_status_change']);
        }
        $affectedRows = 0;
        if (!isset($data['settings'])) {
            $this->db->where('project_id', $id);
            $this->db->update('tblprojectsettings', array(
                'value' => 0,
            ));
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $_settings = array();
            $_values = array();

            foreach ($data['settings'] as $name => $val) {
                array_push($_settings, $name);
                $_values[$name] = $val;
            }

            unset($data['settings']);
            $original_settings = $this->get_project_settings($id);

            foreach ($original_settings as $setting) {
                if ($setting['name'] != 'available_features') {
                    if (in_array($setting['name'], $_settings)) {
                        $value_setting = 1;
                    } else {
                        $value_setting = 0;
                    }
                } else {
                    $tabs = get_project_tabs_admin(null);
                    $tab_settings = array();
                    foreach ($_values[$setting['name']] as $tab) {
                        $tab_settings[$tab] = 1;
                    }
                    foreach ($tabs as $tab) {
                        if (!isset($tab['dropdown'])) {
                            if (!in_array($tab['name'], $_values[$setting['name']])) {
                                $tab_settings[$tab['name']] = 0;
                            }
                        } else {
                            foreach ($tab['dropdown'] as $tab_dropdown) {
                                if (!in_array($tab_dropdown['name'], $_values[$setting['name']])) {
                                    $tab_settings[$tab_dropdown['name']] = 0;
                                }
                            }
                        }
                    }
                    $value_setting = serialize($tab_settings);
                }


                $this->db->where('project_id', $id);
                $this->db->where('name', $setting['name']);
                $this->db->update('tblprojectsettings', array(
                    'value' => $value_setting,
                ));
                if ($this->db->affected_rows() > 0) {
                    $affectedRows++;
                }
            }
        }

        if ($old_status == 4 && $data['status'] != 4) {
            $data['date_finished'] = null;
        } elseif (isset($data['date_finished'])) {
            $data['date_finished'] = to_sql_date($data['date_finished'], true);
        }

        if (isset($data['progress_from_tasks'])) {
            $data['progress_from_tasks'] = 1;
        } else {
            $data['progress_from_tasks'] = 0;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if (!empty($data['deadline'])) {
            $data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $data['deadline'] = null;
        }

        $data['start_date'] = to_sql_date($data['start_date']);
        if ($data['billing_type'] == 1) {
            $data['project_rate_per_hour'] = 0;
        } elseif ($data['billing_type'] == 2) {
            $data['project_cost'] = 0;
        } else {
            $data['project_rate_per_hour'] = 0;
            $data['project_cost']          = 0;
        }
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
            unset($data['project_members']);
        }
        $_pm = array();
        if (isset($project_members)) {
            $_pm['project_members'] = $project_members;
        }
        if ($this->add_edit_members($_pm, $id)) {
            $affectedRows++;
        }
        if (isset($data['mark_all_tasks_as_completed'])) {
            $mark_all_tasks_as_completed = true;
            unset($data['mark_all_tasks_as_completed']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'project')) {
                $affectedRows++;
            }
            unset($data['tags']);
        }

        $_data['data'] = $data;
        $_data['id']   = $id;

        $_data = do_action('before_update_project', $_data);

        $data = $_data['data'];

        $this->db->where('id', $id);
        $this->db->update('tblprojects', $data);

        if ($this->db->affected_rows() > 0) {
            if (isset($mark_all_tasks_as_completed)) {
                $this->_mark_all_project_tasks_as_completed($id);
            }
            $affectedRows++;
        }

        if ($send_created_email == true) {
            if ($this->send_project_customer_email($id, 'assigned-to-project')) {
                $affectedRows++;
            }
        }

        if ($send_project_marked_as_finished_email_to_contacts == true) {
            if ($this->send_project_customer_email($id, 'project-finished-to-customer')) {
                $affectedRows++;
            }
        }
        if ($affectedRows > 0) {
            $this->log_activity($id, 'project_activity_updated');
            logActivity('Project Updated [ID: ' . $id . ']');

            if ($original_project->status != $data['status']) {
                do_action('project_status_changed', array(
                    'status' => $data['status'],
                    'project_id' => $id,
                ));
                // Give space this log to be on top
                sleep(1);
                if ($data['status'] == 4) {
                    $this->log_activity($id, 'project_marked_as_finished');
                    $this->db->where('id', $id);
                    $this->db->update('tblprojects', array('date_finished'=>date('Y-m-d H:i:s')));
                } else {
                    $this->log_activity($id, 'project_status_updated', '<b><lang>project_status_' . $data['status'] . '</lang></b>');
                }

                if (isset($notify_project_members_status_change)) {
                    $this->_notify_project_members_status_change($id, $original_project->status, $data['status']);
                }
            }
            do_action('after_update_project', $id);

            return true;
        }

        return false;
    }

    /**
     * Simplified function to send non complicated email templates for project contacts
     * @param  mixed $id project id
     * @return boolean
     */
    public function send_project_customer_email($id, $template)
    {
        $this->db->select('clientid');
        $this->db->where('id', $id);
        $clientid = $this->db->get('tblprojects')->row()->clientid;

        $sent     = false;
        $contacts = $this->clients_model->get_contacts($clientid, array('active'=>1, 'project_emails'=>1));
        $this->load->model('emails_model');
        foreach ($contacts as $contact) {
            $merge_fields = array();
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($clientid, $contact['id']));
            $merge_fields = array_merge($merge_fields, get_project_merge_fields($id, array(
                    'customer_template' => true,
                )));
            if ($this->emails_model->send_email_template($template, $contact['email'], $merge_fields)) {
                $send = true;
            }
        }

        return $sent;
    }

    public function mark_as($data)
    {
        $this->db->select('status');
        $this->db->where('id', $data['project_id']);
        $old_status = $this->db->get('tblprojects')->row()->status;

        $this->db->where('id', $data['project_id']);
        $this->db->update('tblprojects', array(
            'status' => $data['status_id'],
        ));
        if ($this->db->affected_rows() > 0) {
            do_action('project_status_changed', array(
                'status' => $data['status_id'],
                'project_id' => $data['project_id'],
            ));

            if ($data['status_id'] == 4) {
                $this->log_activity($data['project_id'], 'project_marked_as_finished');
                $this->db->where('id', $data['project_id']);
                $this->db->update('tblprojects', array('date_finished'=>date('Y-m-d H:i:s')));
            } else {
                $this->log_activity($data['project_id'], 'project_status_updated', '<b><lang>project_status_' . $data['status_id'] . '</lang></b>');
                if ($old_status == 4) {
                    $this->db->update('tblprojects', array('date_finished'=>null));
                }
            }

            if ($data['notify_project_members_status_change'] == 1) {
                $this->_notify_project_members_status_change($data['project_id'], $old_status, $data['status_id']);
            }
            if ($data['mark_all_tasks_as_completed'] == 1) {
                $this->_mark_all_project_tasks_as_completed($data['project_id']);
            }

            if (isset($data['send_project_marked_as_finished_email_to_contacts']) && $data['send_project_marked_as_finished_email_to_contacts'] == 1) {
                $this->send_project_customer_email($data['project_id'], 'project-finished-to-customer');
            }

            return true;
        }


        return false;
    }

    private function _notify_project_members_status_change($id, $old_status, $new_status)
    {
        $members = $this->get_project_members($id);
        $notifiedUsers = array();
        foreach ($members as $member) {
            if ($member['staff_id'] != get_staff_user_id()) {
                $notified = add_notification(array(
                    'fromuserid' => get_staff_user_id(),
                    'description' => 'not_project_status_updated',
                    'link' => 'projects/view/' . $id,
                    'touserid' => $member['staff_id'],
                    'additional_data' => serialize(array(
                        '<lang>project_status_' . $old_status . '</lang>',
                        '<lang>project_status_' . $new_status . '</lang>',
                    )),
                ));
                if ($notified) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
        }
        pusher_trigger_notification($notifiedUsers);
    }

    private function _mark_all_project_tasks_as_completed($id)
    {
        $this->db->where('rel_type', 'project');
        $this->db->where('rel_id', $id);
        $this->db->update('tblstafftasks', array(
            'status' => 5,
            'datefinished' => date('Y-m-d H:i:s'),
        ));
        $tasks = $this->get_tasks($id);
        foreach ($tasks as $task) {
            $this->db->where('task_id', $task['id']);
            $this->db->where('end_time IS NULL');
            $this->db->update('tbltaskstimers', array(
                'end_time' => time(),
            ));
        }
        $this->log_activity($id, 'project_activity_marked_all_tasks_as_complete');
    }

    public function add_edit_members($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['project_members'])) {
            $project_members = $data['project_members'];
        }

        $new_project_members_to_receive_email = array();
        $this->db->select('name,clientid');
        $this->db->where('id', $id);
        $project = $this->db->get('tblprojects')->row();
        $project_name = $project->name;
        $client_id = $project->clientid;

        $project_members_in = $this->get_project_members($id);
        if (sizeof($project_members_in) > 0) {
            foreach ($project_members_in as $project_member) {
                if (isset($project_members)) {
                    if (!in_array($project_member['staff_id'], $project_members)) {
                        $this->db->where('project_id', $id);
                        $this->db->where('staff_id', $project_member['staff_id']);
                        $this->db->delete('tblprojectmembers');
                        if ($this->db->affected_rows() > 0) {
                            $this->db->where('staff_id', $project_member['staff_id']);
                            $this->db->where('project_id', $id);
                            $this->db->delete('tblpinnedprojects');

                            $this->log_activity($id, 'project_activity_removed_team_member', get_staff_full_name($project_member['staff_id']));
                            $affectedRows++;
                        }
                    }
                } else {
                    $this->db->where('project_id', $id);
                    $this->db->delete('tblprojectmembers');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if (isset($project_members)) {
                $notifiedUsers = array();
                foreach ($project_members as $staff_id) {
                    $this->db->where('project_id', $id);
                    $this->db->where('staff_id', $staff_id);
                    $_exists = $this->db->get('tblprojectmembers')->row();
                    if (!$_exists) {
                        if (empty($staff_id)) {
                            continue;
                        }
                        $this->db->insert('tblprojectmembers', array(
                            'project_id' => $id,
                            'staff_id' => $staff_id,
                        ));
                        if ($this->db->affected_rows() > 0) {
                            if ($staff_id != get_staff_user_id()) {
                                $notified = add_notification(array(
                                    'fromuserid' => get_staff_user_id(),
                                    'description' => 'not_staff_added_as_project_member',
                                    'link' => 'projects/view/' . $id,
                                    'touserid' => $staff_id,
                                    'additional_data' => serialize(array(
                                        $project_name,
                                    )),
                                ));
                                array_push($new_project_members_to_receive_email, $staff_id);
                                if ($notified) {
                                    array_push($notifiedUsers, $staff_id);
                                }
                            }


                            $this->log_activity($id, 'project_activity_added_team_member', get_staff_full_name($staff_id));
                            $affectedRows++;
                        }
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        } else {
            if (isset($project_members)) {
                $notifiedUsers = array();
                foreach ($project_members as $staff_id) {
                    if (empty($staff_id)) {
                        continue;
                    }
                    $this->db->insert('tblprojectmembers', array(
                        'project_id' => $id,
                        'staff_id' => $staff_id,
                    ));
                    if ($this->db->affected_rows() > 0) {
                        if ($staff_id != get_staff_user_id()) {
                            $notified = add_notification(array(
                                'fromuserid' => get_staff_user_id(),
                                'description' => 'not_staff_added_as_project_member',
                                'link' => 'projects/view/' . $id,
                                'touserid' => $staff_id,
                                'additional_data' => serialize(array(
                                    $project_name,
                                )),
                            ));
                            array_push($new_project_members_to_receive_email, $staff_id);
                            if ($notifiedUsers) {
                                array_push($notifiedUsers, $staff_id);
                            }
                        }
                        $this->log_activity($id, 'project_activity_added_team_member', get_staff_full_name($staff_id));
                        $affectedRows++;
                    }
                }
                pusher_trigger_notification($notifiedUsers);
            }
        }

        if (count($new_project_members_to_receive_email) > 0) {
            $this->load->model('emails_model');
            $all_members = $this->get_project_members($id);
            foreach ($all_members as $data) {
                if (in_array($data['staff_id'], $new_project_members_to_receive_email)) {
                    $merge_fields = array();
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($client_id));
                    $merge_fields = array_merge($merge_fields, get_staff_merge_fields($data['staff_id']));
                    $merge_fields = array_merge($merge_fields, get_project_merge_fields($id));
                    $this->emails_model->send_email_template('staff-added-as-project-member', $data['email'], $merge_fields);
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    public function is_member($project_id, $staff_id = '')
    {
        if (!is_numeric($staff_id)) {
            $staff_id = get_staff_user_id();
        }
        $member = total_rows('tblprojectmembers', array(
            'staff_id' => $staff_id,
            'project_id' => $project_id,
        ));
        if ($member > 0) {
            return true;
        }

        return false;
    }

    public function get_projects_for_ticket($client_id)
    {
        return $this->get('', array(
            'clientid' => $client_id,
        ));
    }

    public function get_project_settings($project_id)
    {
        $this->db->where('project_id', $project_id);

        return $this->db->get('tblprojectsettings')->result_array();
    }

    public function get_project_members($id)
    {
        $this->db->select('email,project_id,staff_id');
        $this->db->join('tblstaff', 'tblstaff.staffid=tblprojectmembers.staff_id');
        $this->db->where('project_id', $id);

        return $this->db->get('tblprojectmembers')->result_array();
    }

    public function remove_team_member($project_id, $staff_id)
    {
        $this->db->where('project_id', $project_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete('tblprojectmembers');
        if ($this->db->affected_rows() > 0) {

            // Remove member from tasks where is assigned
            $this->db->where('staffid', $staff_id);
            $this->db->where('taskid IN (SELECT id FROM tblstafftasks WHERE rel_type="project" AND rel_id="'.$project_id.'")');
            $this->db->delete('tblstafftaskassignees');

            $this->log_activity($project_id, 'project_activity_removed_team_member', get_staff_full_name($staff_id));

            return true;
        }

        return false;
    }

    public function get_timesheets($project_id, $tasks_ids = array())
    {
        if (count($tasks_ids) == 0) {
            $tasks     = $this->get_tasks($project_id);
            $tasks_ids = array();
            foreach ($tasks as $task) {
                array_push($tasks_ids, $task['id']);
            }
        }
        if (count($tasks_ids) > 0) {
            $this->db->where('task_id IN(' . implode(', ', $tasks_ids) . ')');
            $timesheets = $this->db->get('tbltaskstimers')->result_array();
            $i          = 0;
            foreach ($timesheets as $t) {
                $task                         = $this->tasks_model->get($t['task_id']);
                $timesheets[$i]['task_data']  = $task;
                $timesheets[$i]['staff_name'] = get_staff_full_name($t['staff_id']);
                if (!is_null($t['end_time'])) {
                    $timesheets[$i]['total_spent'] = $t['end_time'] - $t['start_time'];
                } else {
                    $timesheets[$i]['total_spent'] = time() - $t['start_time'];
                }
                $i++;
            }

            return $timesheets;
        } else {
            return array();
        }
    }

    public function get_discussion($id, $project_id = '')
    {
        if ($project_id != '') {
            $this->db->where('project_id', $project_id);
        }
        $this->db->where('id', $id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
            $this->db->where('project_id IN (SELECT id FROM tblprojects WHERE clientid=' . get_client_user_id() . ')');
        }
        $discussion = $this->db->get('tblprojectdiscussions')->row();
        if ($discussion) {
            return $discussion;
        }

        return false;
    }

    public function get_discussion_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get('tblprojectdiscussioncomments')->row();
        if ($comment->contact_id != 0) {
            if (is_client_logged_in()) {
                if ($comment->contact_id == get_contact_user_id()) {
                    $comment->created_by_current_user = true;
                } else {
                    $comment->created_by_current_user = false;
                }
            } else {
                $comment->created_by_current_user = false;
            }
            $comment->profile_picture_url = contact_profile_image_url($comment->contact_id);
        } else {
            if (is_client_logged_in()) {
                $comment->created_by_current_user = false;
            } else {
                if (is_staff_logged_in()) {
                    if ($comment->staff_id == get_staff_user_id()) {
                        $comment->created_by_current_user = true;
                    } else {
                        $comment->created_by_current_user = false;
                    }
                } else {
                    $comment->created_by_current_user = false;
                }
            }
            if (is_admin($comment->staff_id)) {
                $comment->created_by_admin = true;
            } else {
                $comment->created_by_admin = false;
            }
            $comment->profile_picture_url = staff_profile_image_url($comment->staff_id);
        }
        $comment->created = (strtotime($comment->created) * 1000);
        if (!empty($comment->modified)) {
            $comment->modified = (strtotime($comment->modified) * 1000);
        }
        if (!is_null($comment->file_name)) {
            $comment->file_url = site_url('uploads/discussions/' . $comment->discussion_id . '/' . $comment->file_name);
        }

        return $comment;
    }

    public function get_discussion_comments($id, $type)
    {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments = $this->db->get('tblprojectdiscussioncomments')->result_array();
        $i        = 0;
        foreach ($comments as $comment) {
            if ($comment['contact_id'] != 0) {
                if (is_client_logged_in()) {
                    if ($comment['contact_id'] == get_contact_user_id()) {
                        $comments[$i]['created_by_current_user'] = true;
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                } else {
                    $comments[$i]['created_by_current_user'] = false;
                }
                $comments[$i]['profile_picture_url'] = contact_profile_image_url($comment['contact_id']);
            } else {
                if (is_client_logged_in()) {
                    $comments[$i]['created_by_current_user'] = false;
                } else {
                    if (is_staff_logged_in()) {
                        if ($comment['staff_id'] == get_staff_user_id()) {
                            $comments[$i]['created_by_current_user'] = true;
                        } else {
                            $comments[$i]['created_by_current_user'] = false;
                        }
                    } else {
                        $comments[$i]['created_by_current_user'] = false;
                    }
                }
                if (is_admin($comment['staff_id'])) {
                    $comments[$i]['created_by_admin'] = true;
                } else {
                    $comments[$i]['created_by_admin'] = false;
                }
                $comments[$i]['profile_picture_url'] = staff_profile_image_url($comment['staff_id']);
            }
            if (!is_null($comment['file_name'])) {
                $comments[$i]['file_url'] = site_url('uploads/discussions/' . $id . '/' . $comment['file_name']);
            }
            $comments[$i]['created'] = (strtotime($comment['created']) * 1000);
            if (!empty($comment['modified'])) {
                $comments[$i]['modified'] = (strtotime($comment['modified']) * 1000);
            }
            $i++;
        }

        return $comments;
    }

    public function get_discussions($project_id)
    {
        $this->db->where('project_id', $project_id);
        if (is_client_logged_in()) {
            $this->db->where('show_to_customer', 1);
        }
        $discussions = $this->db->get('tblprojectdiscussions')->result_array();
        $i           = 0;
        foreach ($discussions as $discussion) {
            $discussions[$i]['total_comments'] = total_rows('tblprojectdiscussioncomments', array(
                'discussion_id' => $discussion['id'],
            ));
            $i++;
        }

        return $discussions;
    }

    public function add_discussion_comment($data, $discussion_id, $type)
    {
        $discussion               = $this->get_discussion($discussion_id);
        $_data['discussion_id']   = $discussion_id;
        $_data['discussion_type'] = $type;
        if (isset($data['content'])) {
            $_data['content'] = $data['content'];
        }
        if (isset($data['parent']) && $data['parent'] != null) {
            $_data['parent'] = $data['parent'];
        }
        if (is_client_logged_in()) {
            $_data['contact_id'] = get_contact_user_id();
            $_data['fullname']   = get_contact_full_name($_data['contact_id']);
            $_data['staff_id']   = 0;
        } else {
            $_data['contact_id'] = 0;
            $_data['staff_id']   = get_staff_user_id();
            $_data['fullname']   = get_staff_full_name($_data['staff_id']);
        }
        $_data            = handle_project_discussion_comment_attachments($discussion_id, $data, $_data);
        $_data['created'] = date('Y-m-d H:i:s');
        $this->db->insert('tblprojectdiscussioncomments', $_data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if ($type == 'regular') {
                $discussion = $this->get_discussion($discussion_id);
                $not_link   = 'projects/view/' . $discussion->project_id . '?group=project_discussions&discussion_id=' . $discussion_id;
            } else {
                $discussion                   = $this->get_file($discussion_id);
                $not_link                     = 'projects/view/' . $discussion->project_id . '?group=project_files&file_id=' . $discussion_id;
                $discussion->show_to_customer = $discussion->visible_to_customer;
            }

            $this->send_project_email_template($discussion->project_id, 'new-project-discussion-comment-to-staff', 'new-project-discussion-comment-to-customer', $discussion->show_to_customer, array(
                'staff' => array(
                    'discussion_id' => $discussion_id,
                    'discussion_comment_id' => $insert_id,
                    'discussion_type' => $type,
                ),
                'customers' => array(
                    'customer_template' => true,
                    'discussion_id' => $discussion_id,
                    'discussion_comment_id' => $insert_id,
                    'discussion_type' => $type,
                ),
            ));


            $this->log_activity($discussion->project_id, 'project_activity_commented_on_discussion', $discussion->subject, $discussion->show_to_customer);

            $notification_data = array(
                'description' => 'not_commented_on_project_discussion',
                'link' => $not_link,
            );

            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }

            $members = $this->get_project_members($discussion->project_id);
            $notifiedUsers = array();
            foreach ($members as $member) {
                if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                    continue;
                }
                $notification_data['touserid'] = $member['staff_id'];
                if (add_notification($notification_data)) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
            pusher_trigger_notification($notifiedUsers);

            $this->_update_discussion_last_activity($discussion_id, $type);

            return $this->get_discussion_comment($insert_id);
        }

        return false;
    }

    public function update_discussion_comment($data)
    {
        $comment = $this->get_discussion_comment($data['id']);
        $this->db->where('id', $data['id']);
        $this->db->update('tblprojectdiscussioncomments', array(
            'modified' => date('Y-m-d H:i:s'),
            'content' => $data['content'],
        ));
        if ($this->db->affected_rows() > 0) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type);
        }

        return $this->get_discussion_comment($data['id']);
    }

    public function delete_discussion_comment($id)
    {
        $comment = $this->get_discussion_comment($id);
        $this->db->where('id', $id);
        $this->db->delete('tblprojectdiscussioncomments');
        if ($this->db->affected_rows() > 0) {
            $this->delete_discussion_comment_attachment($comment->file_name, $comment->discussion_id);

            $additional_data = '';
            if ($comment->discussion_type == 'regular') {
                $discussion = $this->get_discussion($comment->discussion_id);
                $not        = 'project_activity_deleted_discussion_comment';
                $additional_data .= $discussion->subject . '<br />' . $comment->content;
            } else {
                $discussion = $this->get_file($comment->discussion_id);
                $not        = 'project_activity_deleted_file_discussion_comment';
                $additional_data .= $discussion->subject . '<br />' . $comment->content;
            }

            if (!is_null($comment->file_name)) {
                $additional_data .= $comment->file_name;
            }
            $this->log_activity($discussion->project_id, $not, $additional_data);
        }
        $this->db->where('parent', $id);
        $this->db->update('tblprojectdiscussioncomments', array(
            'parent' => null,
        ));
        if ($this->db->affected_rows() > 0) {
            $this->_update_discussion_last_activity($comment->discussion_id, $comment->discussion_type);
        }

        return true;
    }

    public function delete_discussion_comment_attachment($file_name, $discussion_id)
    {
        $path = PROJECT_DISCUSSION_ATTACHMENT_FOLDER . $discussion_id;
        if (!is_null($file_name)) {
            if (file_exists($path . '/' . $file_name)) {
                unlink($path . '/' . $file_name);
            }
        }
        if (is_dir($path)) {
            // Check if no attachments left, so we can delete the folder also
            $other_attachments = list_files($path);
            if (count($other_attachments) == 0) {
                delete_dir($path);
            }
        }
    }

    public function add_discussion($data)
    {
        if (is_client_logged_in()) {
            $data['contact_id']       = get_contact_user_id();
            $data['staff_id']         = 0;
            $data['show_to_customer'] = 1;
        } else {
            $data['staff_id']   = get_staff_user_id();
            $data['contact_id'] = 0;
            if (isset($data['show_to_customer'])) {
                $data['show_to_customer'] = 1;
            } else {
                $data['show_to_customer'] = 0;
            }
        }
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['description'] = nl2br($data['description']);
        $this->db->insert('tblprojectdiscussions', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $members           = $this->get_project_members($data['project_id']);
            $notification_data = array(
                'description' => 'not_created_new_project_discussion',
                'link' => 'projects/view/' . $data['project_id'] . '?group=project_discussions&discussion_id=' . $insert_id,
            );

            if (is_client_logged_in()) {
                $notification_data['fromclientid'] = get_contact_user_id();
            } else {
                $notification_data['fromuserid'] = get_staff_user_id();
            }

            $notifiedUsers = array();
            foreach ($members as $member) {
                if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                    continue;
                }
                $notification_data['touserid'] = $member['staff_id'];
                if (add_notification($notification_data)) {
                    array_push($notifiedUsers, $member['staff_id']);
                }
            }
            pusher_trigger_notification($notifiedUsers);
            $this->send_project_email_template($data['project_id'], 'new-project-discussion-created-to-staff', 'new-project-discussion-created-to-customer', $data['show_to_customer'], array(
                'staff' => array(
                    'discussion_id' => $insert_id,
                    'discussion_type' => 'regular',
                ),
                'customers' => array(
                    'customer_template' => true,
                    'discussion_id' => $insert_id,
                    'discussion_type' => 'regular',
                ),
            ));
            $this->log_activity($data['project_id'], 'project_activity_created_discussion', $data['subject'], $data['show_to_customer']);

            return $insert_id;
        }

        return false;
    }

    public function edit_discussion($data, $id)
    {
        $this->db->where('id', $id);
        if (isset($data['show_to_customer'])) {
            $data['show_to_customer'] = 1;
        } else {
            $data['show_to_customer'] = 0;
        }
        $data['description'] = nl2br($data['description']);
        $this->db->update('tblprojectdiscussions', $data);
        if ($this->db->affected_rows() > 0) {
            $this->log_activity($data['project_id'], 'project_activity_updated_discussion', $data['subject'], $data['show_to_customer']);

            return true;
        }

        return false;
    }

    public function delete_discussion($id)
    {
        $discussion = $this->get_discussion($id);
        $this->db->where('id', $id);
        $this->db->delete('tblprojectdiscussions');
        if ($this->db->affected_rows() > 0) {
            $this->log_activity($discussion->project_id, 'project_activity_deleted_discussion', $discussion->subject, $discussion->show_to_customer);
            $this->_delete_discussion_comments($id, 'regular');

            return true;
        }

        return false;
    }

    public function copy($project_id, $data)
    {
        $project   = $this->get($project_id);
        $settings  = $this->get_project_settings($project_id);
        $_new_data = array();
        $fields    = $this->db->list_fields('tblprojects');
        foreach ($fields as $field) {
            if (isset($project->$field)) {
                $_new_data[$field] = $project->$field;
            }
        }

        unset($_new_data['id']);
        $_new_data['clientid'] = $data['clientid_copy_project'];
        unset($_new_data['clientid_copy_project']);

        $_new_data['start_date'] = to_sql_date($data['start_date']);

        if ($_new_data['start_date'] > date('Y-m-d')) {
            $_new_data['status'] = 1;
        } else {
            $_new_data['status'] = 2;
        }
        if ($data['deadline']) {
            $_new_data['deadline'] = to_sql_date($data['deadline']);
        } else {
            $_new_data['deadline'] = null;
        }

        $_new_data['project_created'] = date('Y-m-d H:i:s');
        $_new_data['addedfrom']       = get_staff_user_id();

        $_new_data['date_finished'] = null;

        $this->db->insert('tblprojects', $_new_data);
        $id = $this->db->insert_id();
        if ($id) {
            $tags = get_tags_in($project_id, 'project');
            handle_tags_save($tags, $id, 'project');

            foreach ($settings as $setting) {
                $this->db->insert('tblprojectsettings', array(
                    'project_id' => $id,
                    'name' => $setting['name'],
                    'value' => $setting['value'],
                ));
            }
            $added_tasks = array();
            $tasks       = $this->get_tasks($project_id);
            if (isset($data['tasks'])) {
                foreach ($tasks as $task) {
                    if (isset($data['task_include_followers'])) {
                        $copy_task_data['copy_task_followers'] = 'true';
                    }
                    if (isset($data['task_include_assignees'])) {
                        $copy_task_data['copy_task_assignees'] = 'true';
                    }
                    if (isset($data['tasks_include_checklist_items'])) {
                        $copy_task_data['copy_task_checklist_items'] = 'true';
                    }
                    $copy_task_data['copy_from'] = $task['id'];
                    $task_id                     = $this->tasks_model->copy($copy_task_data, array(
                        'rel_id' => $id,
                        'rel_type' => 'project',
                        'last_recurring_date' => null,
                        'status'=>$data['copy_project_task_status'],
                    ));
                    if ($task_id) {
                        array_push($added_tasks, $task_id);
                    }
                }
            }
            if (isset($data['milestones'])) {
                $milestones        = $this->get_milestones($project_id);
                $_added_milestones = array();
                foreach ($milestones as $milestone) {
                    $dCreated = new DateTime($milestone['datecreated']);
                    $dDuedate = new DateTime($milestone['due_date']);
                    $dDiff    = $dCreated->diff($dDuedate);
                    $due_date = date('Y-m-d', strtotime(date('Y-m-d', strtotime('+' . $dDiff->days . 'DAY'))));

                    $this->db->insert('tblmilestones', array(
                        'name' => $milestone['name'],
                        'project_id' => $id,
                        'milestone_order' => $milestone['milestone_order'],
                        'description_visible_to_customer' => $milestone['description_visible_to_customer'],
                        'description' => $milestone['description'],
                        'due_date' => $due_date,
                        'datecreated' => date('Y-m-d'),
                        'color' => $milestone['color'],
                    ));

                    $milestone_id = $this->db->insert_id();
                    if ($milestone_id) {
                        $_added_milestone_data         = array();
                        $_added_milestone_data['id']   = $milestone_id;
                        $_added_milestone_data['name'] = $milestone['name'];
                        $_added_milestones[]           = $_added_milestone_data;
                    }
                }
                if (isset($data['tasks'])) {
                    if (count($added_tasks) > 0) {
                        // Original project tasks
                        foreach ($tasks as $task) {
                            if ($task['milestone'] != 0) {
                                $this->db->where('id', $task['milestone']);
                                $milestone = $this->db->get('tblmilestones')->row();
                                if ($milestone) {
                                    $name = $milestone->name;
                                    foreach ($_added_milestones as $added_milestone) {
                                        if ($name == $added_milestone['name']) {
                                            $this->db->where('id IN (' . implode(', ', $added_tasks) . ')');
                                            $this->db->where('milestone', $task['milestone']);
                                            $this->db->update('tblstafftasks', array(
                                                'milestone' => $added_milestone['id'],
                                            ));
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // milestones not set
                if (count($added_tasks)) {
                    foreach ($added_tasks as $task) {
                        $this->db->where('id', $task['id']);
                        $this->db->update('tblstafftasks', array(
                            'milestone' => 0,
                        ));
                    }
                }
            }
            if (isset($data['members'])) {
                $members  = $this->get_project_members($project_id);
                $_members = array();
                foreach ($members as $member) {
                    array_push($_members, $member['staff_id']);
                }
                $this->add_edit_members(array(
                    'project_members' => $_members,
                ), $id);
            }

            $this->log_activity($id, 'project_activity_created');
            logActivity('Project Copied [ID: ' . $project_id . ', NewID: ' . $id . ']');

            return $id;
        }

        return false;
    }

    public function get_staff_notes($project_id)
    {
        $this->db->where('project_id', $project_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get('tblprojectnotes')->row();
        if ($notes) {
            return $notes->content;
        }

        return '';
    }

    public function save_note($data, $project_id)
    {
        // Check if the note exists for this project;
        $this->db->where('project_id', $project_id);
        $this->db->where('staff_id', get_staff_user_id());
        $notes = $this->db->get('tblprojectnotes')->row();
        if ($notes) {
            $this->db->where('id', $notes->id);
            $this->db->update('tblprojectnotes', array(
                'content' => $data['content'],
            ));
            if ($this->db->affected_rows() > 0) {
                return true;
            }

            return false;
        } else {
            $this->db->insert('tblprojectnotes', array(
                'staff_id' => get_staff_user_id(),
                'content' => $data['content'],
                'project_id' => $project_id,
            ));
            $insert_id = $this->db->insert_id();
            if ($insert_id) {
                return true;
            }

            return false;
        }

        return false;
    }

    public function delete($project_id)
    {
        $project_name = get_project_name_by_id($project_id);

        $this->db->where('id', $project_id);
        $this->db->delete('tblprojects');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('project_id', $project_id);
            $this->db->delete('tblprojectmembers');
            $this->db->where('project_id', $project_id);
            $this->db->delete('tblprojectnotes');

            $this->db->where('project_id', $project_id);
            $this->db->delete('tblmilestones');

            // Delete the custom field values
            $this->db->where('relid', $project_id);
            $this->db->where('fieldto', 'projects');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('rel_id', $project_id);
            $this->db->where('rel_type', 'project');
            $this->db->delete('tbltags_in');



            $this->db->where('project_id', $project_id);
            $discussions = $this->db->get('tblprojectdiscussions')->result_array();
            foreach ($discussions as $discussion) {
                $discussion_comments = $this->get_discussion_comments($discussion['id'], 'regular');
                foreach ($discussion_comments as $comment) {
                    $this->delete_discussion_comment_attachment($comment['file_name'], $discussion['id']);
                }
                $this->db->where('discussion_id', $discussion['id']);
                $this->db->delete('tblprojectdiscussioncomments');
            }
            $this->db->where('project_id', $project_id);
            $this->db->delete('tblprojectdiscussions');
            $files = $this->get_files($project_id);
            foreach ($files as $file) {
                $this->remove_file($file['id']);
            }
            $tasks = $this->get_tasks($project_id);
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false);
            }

            $this->db->where('project_id', $project_id);
            $this->db->delete('tblprojectactivity');

            $this->db->where('project_id', $project_id);
            $this->db->update('tblexpenses', array(
                'project_id' => 0,
            ));

            $this->db->where('project_id', $project_id);
            $this->db->update('tblinvoices', array(
                'project_id' => 0,
            ));

            $this->db->where('project_id', $project_id);
            $this->db->update('tblcreditnotes', array(
                'project_id' => 0,
            ));

            $this->db->where('project_id', $project_id);
            $this->db->update('tblestimates', array(
                'project_id' => 0,
            ));

            $this->db->where('project_id', $project_id);
            $this->db->update('tbltickets', array(
                'project_id' => 0,
            ));

            $this->db->where('project_id', $project_id);
            $this->db->delete('tblpinnedprojects');

            logActivity('Project Deleted [ID: ' . $project_id . ', Name: ' . $project_name . ']');

            return true;
        }

        return false;
    }

    public function get_activity($id = '', $limit = '', $only_project_members_activity = false)
    {
        if (!is_client_logged_in()) {
            $has_permission = has_permission('projects', '', 'view');
            if (!$has_permission) {
                $this->db->where('project_id IN (SELECT project_id FROM tblprojectmembers WHERE staff_id=' . get_staff_user_id() . ')');
            }
        }
        if (is_client_logged_in()) {
            $this->db->where('visible_to_customer', 1);
        }
        if (is_numeric($id)) {
            $this->db->where('project_id', $id);
        }
        if (is_numeric($limit)) {
            $this->db->limit($limit);
        }
        $this->db->order_by('dateadded', 'desc');
        $activities = $this->db->get('tblprojectactivity')->result_array();
        $i          = 0;
        foreach ($activities as $activity) {
            $seconds          = get_string_between($activity['additional_data'], '<seconds>', '</seconds>');
            $other_lang_keys  = get_string_between($activity['additional_data'], '<lang>', '</lang>');
            $_additional_data = $activity['additional_data'];
            if ($seconds != '') {
                $_additional_data = str_replace('<seconds>' . $seconds . '</seconds>', seconds_to_time_format($seconds), $_additional_data);
            }
            if ($other_lang_keys != '') {
                $_additional_data = str_replace('<lang>' . $other_lang_keys . '</lang>', _l($other_lang_keys), $_additional_data);
            }
            if (strpos($_additional_data, 'project_status_') !== false) {
                $_additional_data = get_project_status_by_id(strafter($_additional_data, 'project_status_'));
            }
            $activities[$i]['description']     = _l($activities[$i]['description_key']);
            $activities[$i]['additional_data'] = $_additional_data;
            $activities[$i]['project_name'] = get_project_name_by_id($activity['project_id']);
            unset($activities[$i]['description_key']);
            $i++;
        }

        return $activities;
    }

    public function log_activity($project_id, $description_key, $additional_data = '', $visible_to_customer = 1)
    {
        if (!DEFINED('CRON')) {
            if (is_client_logged_in()) {
                $data['contact_id'] = get_contact_user_id();
                $data['staff_id']   = 0;
                $data['fullname']   = get_contact_full_name(get_contact_user_id());
            } elseif (is_staff_logged_in()) {
                $data['contact_id'] = 0;
                $data['staff_id']   = get_staff_user_id();
                $data['fullname']   = get_staff_full_name(get_staff_user_id());
            }
        } else {
            $data['contact_id'] = 0;
            $data['staff_id']   = 0;
            $data['fullname']   = '[CRON]';
        }
        $data['description_key']     = $description_key;
        $data['additional_data']     = $additional_data;
        $data['visible_to_customer'] = $visible_to_customer;
        $data['project_id']          = $project_id;
        $data['dateadded']           = date('Y-m-d H:i:s');

        $data = do_action('before_log_project_activity', $data);

        $this->db->insert('tblprojectactivity', $data);
    }

    public function new_project_file_notification($file_id, $project_id)
    {
        $file = $this->get_file($file_id);

        $additional_data = $file->file_name;
        $this->log_activity($project_id, 'project_activity_uploaded_file', $additional_data, $file->visible_to_customer);

        $members = $this->get_project_members($project_id);
        $notification_data = array(
           'description'=>'not_project_file_uploaded',
           'link'=>'projects/view/'.$project_id.'?group=project_files&file_id='.$file_id,
           );

        if (is_client_logged_in()) {
            $notification_data['fromclientid'] = get_contact_user_id();
        } else {
            $notification_data['fromuserid'] = get_staff_user_id();
        }

        $notifiedUsers = array();
        foreach ($members as $member) {
            if ($member['staff_id'] == get_staff_user_id() && !is_client_logged_in()) {
                continue;
            }
            $notification_data['touserid'] = $member['staff_id'];
            if (add_notification($notification_data)) {
                array_push($notifiedUsers, $member['staff_id']);
            }
        }
        pusher_trigger_notification($notifiedUsers);

        $this->send_project_email_template(
           $project_id,
           'new-project-file-uploaded-to-staff',
           'new-project-file-uploaded-to-customer',
           $file->visible_to_customer,
           array(
            'staff'=>array('discussion_id'=>$file_id, 'discussion_type'=>'file'),
            'customers'=>array('customer_template'=>true, 'discussion_id'=>$file_id, 'discussion_type'=>'file'),
            )
           );
    }

    public function add_external_file($data)
    {
        $insert['dateadded'] = date('Y-m-d H:i:s');
        $insert['project_id'] = $data['project_id'];
        $insert['external'] = $data['external'];
        $insert['visible_to_customer'] = $data['visible_to_customer'];
        $insert['file_name'] = $data['files'][0]['name'];
        $insert['subject'] = $data['files'][0]['name'];
        $insert['external_link'] = $data['files'][0]['link'];

        $path_parts            = pathinfo($data['files'][0]['name']);
        $insert['filetype']      = get_mime_by_extension('.' . $path_parts['extension']);

        if (isset($data['files'][0]['thumbnailLink'])) {
            $insert['thumbnail_link'] = $data['files'][0]['thumbnailLink'];
        }

        if (isset($data['staffid'])) {
            $insert['staffid'] = $data['staffid'];
        } elseif (isset($data['contact_id'])) {
            $insert['contact_id'] = $data['contact_id'];
        }

        $this->db->insert('tblprojectfiles', $insert);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $this->new_project_file_notification($insert_id, $data['project_id']);

            return $insert_id;
        }

        return false;
    }

    public function send_project_email_template($project_id, $staff_template, $customer_template, $action_visible_to_customer, $additional_data = array())
    {
        if (count($additional_data) == 0) {
            $additional_data['customers'] = array();
            $additional_data['staff']     = array();
        } elseif (count($additional_data) == 1) {
            if (!isset($additional_data['staff'])) {
                $additional_data['staff'] = array();
            } else {
                $additional_data['customers'] = array();
            }
        }

        $project = $this->get($project_id);
        $members = $this->get_project_members($project_id);

        $this->load->model('emails_model');
        foreach ($members as $member) {
            if (is_staff_logged_in()) {
                if ($member['staff_id'] == get_staff_user_id()) {
                    continue;
                }
            }
            $merge_fields = array();
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($project->clientid));
            $merge_fields = array_merge($merge_fields, get_staff_merge_fields($member['staff_id']));
            $merge_fields = array_merge($merge_fields, get_project_merge_fields($project->id, $additional_data['staff']));
            $this->emails_model->send_email_template($staff_template, $member['email'], $merge_fields);
        }
        if ($action_visible_to_customer == 1) {
            $contacts = $this->clients_model->get_contacts($project->clientid, array('active'=>1, 'project_emails'=>1));
            foreach ($contacts as $contact) {
                if (is_client_logged_in()) {
                    if ($contact['id'] == get_contact_user_id()) {
                        continue;
                    }
                }
                $merge_fields = array();
                $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($project->clientid, $contact['id']));
                $merge_fields = array_merge($merge_fields, get_project_merge_fields($project->id, $additional_data['customers']));
                $this->emails_model->send_email_template($customer_template, $contact['email'], $merge_fields);
            }
        }
    }

    private function _get_project_billing_data($id)
    {
        $this->db->select('billing_type,project_rate_per_hour');
        $this->db->where('id', $id);

        return $this->db->get('tblprojects')->row();
    }

    public function total_logged_time_by_billing_type($id, $conditions = array())
    {
        $project_data = $this->_get_project_billing_data($id);
        $data         = array();
        if ($project_data->billing_type == 2) {
            $seconds             = $this->total_logged_time($id);
            $data                = $this->projects_model->calculate_total_by_project_hourly_rate($seconds, $project_data->project_rate_per_hour);
            $data['logged_time'] = $data['hours'];
        } elseif ($project_data->billing_type == 3) {
            $data = $this->_get_data_total_logged_time($id);
        }

        return $data;
    }

    public function data_billable_time($id)
    {
        return $this->_get_data_total_logged_time($id, array(
            'billable' => 1,
        ));
    }

    public function data_billed_time($id)
    {
        return $this->_get_data_total_logged_time($id, array(
            'billable' => 1,
            'billed' => 1,
        ));
    }

    public function data_unbilled_time($id)
    {
        return $this->_get_data_total_logged_time($id, array(
            'billable' => 1,
            'billed' => 0,
        ));
    }

    private function _delete_discussion_comments($id, $type)
    {
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $comments = $this->db->get('tblprojectdiscussioncomments')->result_array();
        foreach ($comments as $comment) {
            $this->delete_discussion_comment_attachment($comment['file_name'], $id);
        }
        $this->db->where('discussion_id', $id);
        $this->db->where('discussion_type', $type);
        $this->db->delete('tblprojectdiscussioncomments');
    }

    private function _get_data_total_logged_time($id, $conditions = array())
    {
        $project_data = $this->_get_project_billing_data($id);
        $tasks        = $this->get_tasks($id, $conditions);

        if ($project_data->billing_type == 3) {
            $data                = $this->calculate_total_by_task_hourly_rate($tasks);
            $data['logged_time'] = seconds_to_time_format($data['total_seconds']);
        } elseif ($project_data->billing_type == 2) {
            $seconds = 0;
            foreach ($tasks as $task) {
                $seconds += $task['total_logged_time'];
            }
            $data                = $this->calculate_total_by_project_hourly_rate($seconds, $project_data->project_rate_per_hour);
            $data['logged_time'] = $data['hours'];
        }

        return $data;
    }

    private function _update_discussion_last_activity($id, $type)
    {
        if ($type == 'file') {
            $table = 'tblprojectfiles';
        } elseif ($type == 'regular') {
            $table = 'tblprojectdiscussions';
        }
        $this->db->where('id', $id);
        $this->db->update($table, array(
            'last_activity' => date('Y-m-d H:i:s'),
        ));
    }
}
