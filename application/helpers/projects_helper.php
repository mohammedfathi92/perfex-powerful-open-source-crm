<?php

/**
 * Default project tabs
 * @param  mixed $project_id project id to format the url
 * @return array
 */
function get_project_tabs_admin($project_id)
{
    $project_tabs = array(
    array(
        'name'=>'project_overview',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_overview'),
        'icon'=>'fa fa-th',
        'lang'=>_l('project_overview'),
        'visible'=>true,
        'order'=>1,
        ),
    array(
        'name'=>'project_tasks',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_tasks'),
        'icon'=>'fa fa-check-circle',
        'lang'=>_l('tasks'),
        'visible'=>true,
        'order'=>2,
        'linked_to_customer_option'=>array('view_tasks'),
        ),
    array(
        'name'=>'project_timesheets',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_timesheets'),
        'icon'=>'fa fa-clock-o',
        'lang'=>_l('project_timesheets'),
        'visible'=>true,
        'order'=>3,
        'linked_to_customer_option'=>array('view_timesheets'),
        ),
    array(
        'name'=>'project_milestones',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_milestones'),
        'icon'=>'fa fa-rocket',
        'lang'=>_l('project_milestones'),
        'visible'=>true,
        'order'=>4,
        'linked_to_customer_option'=>array('view_milestones'),
        ),
    array(
        'name'=>'project_files',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_files'),
        'icon'=>'fa fa-files-o',
        'lang'=>_l('project_files'),
        'visible'=>true,
        'order'=>5,
        'linked_to_customer_option'=>array('upload_files'),
        ),
    array(
        'name'=>'project_discussions',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_discussions'),
        'icon'=>'fa fa-commenting',
        'lang'=>_l('project_discussions'),
        'visible'=>true,
        'order'=>6,
        'linked_to_customer_option'=>array('open_discussions'),
        ),
    array(
        'name'=>'project_gantt',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_gantt'),
        'icon'=>'fa fa-line-chart',
        'lang'=>_l('project_gant'),
        'visible'=>true,
        'order'=>7,
        'linked_to_customer_option'=>array('view_gantt'),
        ),
    array(
        'name'=>'project_tickets',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_tickets'),
        'icon'=>'fa fa-life-ring',
        'lang'=>_l('project_tickets'),
        'visible'=>(get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member(),
        'order'=>8,
        ),
    array(
        'name'=>"sales",
        'url'=>'#',
        'icon'=>'',
        'lang'=>_l('sales_string'),
        'visible'=>(has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own')) || (has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own')) || (has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own')),
        'order'=>9,
        'dropdown'=>array(
          array(
            'name'=>'project_invoices',
            'url'=>admin_url('projects/view/'.$project_id.'?group=project_invoices'),
            'icon'=>'fa fa-sun-o',
            'lang'=>_l('project_invoices'),
            'visible'=>has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own'),
            'order'=>1,
            ),
          array(
            'name'=>'project_estimates',
            'url'=>admin_url('projects/view/'.$project_id.'?group=project_estimates'),
            'icon'=>'fa fa-sun-o',
            'lang'=>_l('estimates'),
            'visible'=>has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own'),
            'order'=>2,
            ),
          array(
            'name'=>'project_expenses',
            'url'=>admin_url('projects/view/'.$project_id.'?group=project_expenses'),
            'icon'=>'fa fa-sort-amount-asc',
            'lang'=>_l('project_expenses'),
            'visible'=>has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own'),
            'order'=>3,
            ),
          array(
            'name'=>'project_credit_notes',
            'url'=>admin_url('projects/view/'.$project_id.'?group=project_credit_notes'),
            'icon'=>'fa fa-sort-amount-asc',
            'lang'=>_l('credit_notes'),
            'visible'=>has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own'),
            'order'=>3,
            ),
          ),
        ),
    array(
        'name'=>'project_notes',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_notes'),
        'icon'=>'fa fa-clock-o',
        'lang'=>_l('project_notes'),
        'visible'=>true,
        'order'=>10,
        ),
    array(
        'name'=>'project_activity',
        'url'=>admin_url('projects/view/'.$project_id.'?group=project_activity'),
        'icon'=>'fa fa-exclamation',
        'lang'=>_l('project_activity'),
        'visible'=>has_permission('projects', '', 'create'),
        'order'=>11,
        'linked_to_customer_option'=>array('view_activity_log'),
        ),
    );

    $project_tabs = do_action('project_tabs_admin', $project_tabs);

    usort($project_tabs, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $project_tabs;
}

/**
 * Get project status by passed project id
 * @param  mixed $id project id
 * @return array
 */
function get_project_status_by_id($id)
{
    $CI = &get_instance();
    if (!class_exists('projects_model')) {
        $CI->load->model('projects_model');
    }

    $statuses = $CI->projects_model->get_project_statuses();

    $status = array(
      'id'=>0,
      'bg_color'=>'#333',
      'text_color'=>'#333',
      'name'=>'[Status Not Found]',
      'order'=>1,
      );

    foreach ($statuses as $s) {
        if ($s['id'] == $id) {
            $status = $s;
            break;
        }
    }

    return $status;
}

/**
 * Return logged in user pinned projects
 * @return array
 */
function get_user_pinned_projects()
{
    $CI = &get_instance();
    $CI->db->select('tblprojects.id, tblprojects.name, tblprojects.clientid, ' . get_sql_select_client_company());
    $CI->db->join('tblprojects', 'tblprojects.id=tblpinnedprojects.project_id');
    $CI->db->join('tblclients', 'tblclients.userid=tblprojects.clientid');
    $CI->db->where('tblpinnedprojects.staff_id', get_staff_user_id());
    $projects = $CI->db->get('tblpinnedprojects')->result_array();
    $CI->load->model('projects_model');
    $i        = 0;
    foreach ($projects as $project) {
        $projects[$i]['progress'] = $CI->projects_model->calc_progress($project['id']);
        $i++;
    }

    return $projects;
}


/**
 * Get project name by passed id
 * @param  mixed $id
 * @return string
 */
function get_project_name_by_id($id)
{
    $CI =& get_instance();
    $project = $CI->object_cache->get('project-name-data-'.$id);

    if(!$project){
        $CI->db->select('name');
        $CI->db->where('id', $id);
        $project = $CI->db->get('tblprojects')->row();
        $CI->object_cache->add('project-name-data-'.$id,$project);
    }

    if ($project) {
        return $project->name;
    }

    return '';
}

/**
 * Return project milestones
 * @param  mixed $project_id project id
 * @return array
 */
function get_project_milestones($project_id) {
    $CI = &get_instance();
    $CI->db->where('project_id', $project_id);
    $CI->db->order_by('milestone_order', 'ASC');
    return $CI->db->get('tblmilestones')->result_array();
}

/**
 * Get project client id by passed project id
 * @param  mixed $id project id
 * @return mixed
 */
function get_client_id_by_project_id($id)
{
    $CI =& get_instance();
    $CI->db->select('clientid');
    $CI->db->where('id', $id);
    $project = $CI->db->get('tblprojects')->row();
    if ($project) {
        return $project->clientid;
    }

    return false;
}

/**
 * Check if customer has project assigned
 * @param  mixed $customer_id customer id to check
 * @return boolean
 */
function customer_has_projects($customer_id)
{
    $totalCustomerProjects = total_rows('tblprojects', 'clientid='.$customer_id);

    return ($totalCustomerProjects > 0 ? true : false);
}

/**
 * Get projcet billing type
 * @param  mixed $project_id
 * @return mixed
 */
function get_project_billing_type($project_id)
{
    $CI =& get_instance();
    $CI->db->where('id', $project_id);
    $project = $CI->db->get('tblprojects')->row();
    if ($project) {
        return $project->billing_type;
    }

    return false;
}

/**
 * Translated jquery-comment language based on app languages
 * This feature is used on both admin and customer area
 * @return array
 */
function get_project_discussions_language_array()
{
    $lang = array(
        'discussion_add_comment' => _l('discussion_add_comment'),
        'discussion_newest' => _l('discussion_newest'),
        'discussion_oldest' => _l('discussion_oldest'),
        'discussion_attachments' => _l('discussion_attachments'),
        'discussion_send' => _l('discussion_send'),
        'discussion_reply' => _l('discussion_reply'),
        'discussion_edit' => _l('discussion_edit'),
        'discussion_edited' => _l('discussion_edited'),
        'discussion_you' => _l('discussion_you'),
        'discussion_save' => _l('discussion_save'),
        'discussion_delete' => _l('discussion_delete'),
        'discussion_view_all_replies' => _l('discussion_view_all_replies'),
        'discussion_hide_replies' => _l('discussion_hide_replies'),
        'discussion_no_comments' => _l('discussion_no_comments'),
        'discussion_no_attachments' => _l('discussion_no_attachments'),
        'discussion_attachments_drop' => _l('discussion_attachments_drop'),
    );

    return $lang;
}
