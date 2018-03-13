<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Check if field is used in table
 * @param  string  $field column
 * @param  string  $table table name to check
 * @param  integer  $id   ID used
 * @return boolean
 */
function is_reference_in_table($field, $table, $id)
{
    $CI =& get_instance();
    $CI->db->where($field, $id);
    $row = $CI->db->get($table)->row();
    if ($row) {
        return true;
    }

    return false;
}
/**
 * Function that add views tracking for proposals,estimates,invoices,knowledgebase article in database.
 * This function tracks activity only per hour
 * Eq customer viewed invoice at 15:00 and then 15:05 the activity will be tracked only once.
 * If customer view the invoice again in 16:01 there will be activity tracked.
 * @param string $rel_type
 * @param mixed $rel_id
 */
function add_views_tracking($rel_type, $rel_id)
{
    $CI =& get_instance();
    if (!is_staff_logged_in()) {
        $CI->db->where('rel_id', $rel_id);
        $CI->db->where('rel_type', $rel_type);
        $CI->db->order_by('id', 'DESC');
        $CI->db->limit(1);
        $row = $CI->db->get('tblviewstracking')->row();
        if ($row) {
            $dateFromDatabase = strtotime($row->date);
            $date1HourAgo     = strtotime("-1 hours");
            if ($dateFromDatabase >= $date1HourAgo) {
                return false;
            }
        }
    } else {
        // Staff logged in, nothing to do here
        return false;
    }

    do_action('before_insert_views_tracking', array(
    'rel_id' => $rel_id,
    'rel_type' => $rel_type,
    ));

    $notifiedUsers = array();
    $members = array();
    $notification_data = array();
    if ($rel_type == 'invoice' || $rel_type == 'proposal' || $rel_type == 'estimate') {
        $responsible_column = 'sale_agent';

        if ($rel_type == 'invoice') {
            $table = 'tblinvoices';
            $notification_link = 'invoices/list_invoices/' . $rel_id;
            $notification_description = 'not_customer_viewed_invoice';
            array_push($notification_data, format_invoice_number($rel_id));
        } elseif ($rel_type == 'estimate') {
            $table = 'tblestimates';
            $notification_link = 'estimates/list_estimates/' . $rel_id;
            $notification_description = 'not_customer_viewed_estimate';
            array_push($notification_data, format_estimate_number($rel_id));
        } else {
            $responsible_column = 'assigned';
            $table = 'tblproposals';
            $notification_description = 'not_customer_viewed_proposal';
            $notification_link = 'proposals/list_proposals/' . $rel_id;
            array_push($notification_data, format_proposal_number($rel_id));
        }

        $notification_data = serialize($notification_data);

        $CI->db->select('addedfrom,'.$responsible_column)
    ->where('id', $rel_id);

        $rel = $CI->db->get($table)->row();

        $CI->db->select('staffid')
    ->where('staffid', $rel->addedfrom)
    ->or_where('staffid', $rel->{$responsible_column});

        $members = $CI->db->get('tblstaff')->result_array();
    }

    $CI->db->insert('tblviewstracking', array(
    'rel_id' => $rel_id,
    'rel_type' => $rel_type,
    'date' => date('Y-m-d H:i:s'),
    'view_ip' => $CI->input->ip_address(),
    ));

    $view_id = $CI->db->insert_id();
    if ($view_id) {
        foreach ($members as $member) {
            $notification = array(
            'fromcompany' => true,
            'touserid' => $member['staffid'],
            'description' => $notification_description,
            'link' => $notification_link,
            'additional_data' => $notification_data,
            );
            if (is_client_logged_in()) {
                unset($notification['fromcompany']);
            }
            $notified = add_notification($notification);
            if ($notified) {
                array_push($notifiedUsers, $member['staffid']);
            }
        }
        pusher_trigger_notification($notifiedUsers);
    }
}
/**
 * Get views tracking based on rel type and rel id
 * @param  string $rel_type
 * @param  mixed $rel_id
 * @return array
 */
function get_views_tracking($rel_type, $rel_id)
{
    $CI =& get_instance();
    $CI->db->where('rel_id', $rel_id);
    $CI->db->where('rel_type', $rel_type);
    $CI->db->order_by('date', 'DESC');

    return $CI->db->get('tblviewstracking')->result_array();
}

/**
 * Add option in table
 * @since  Version 1.0.1
 * @param string $name  option name
 * @param string $value option value
 */
function add_option($name, $value = '', $autoload = 1)
{
    $CI =& get_instance();

    $exists = total_rows('tbloptions', array(
        'name' => $name,
    ));

    if ($exists == 0) {
        $newData = array(
                'name' => $name,
                'value' => $value,
            );

        if ($CI->db->field_exists('autoload', 'tbloptions')) {
            $newData['autoload'] = $autoload;
        }

        $CI->db->insert('tbloptions', $newData);
        $insert_id = $CI->db->insert_id();

        if ($insert_id) {
            return true;
        }

        return false;
    }

    return false;
}

/**
 * Get option value
 * @param  string $name Option name
 * @return mixed
 */
function get_option($name)
{
    $CI =& get_instance();
    if (!class_exists('app')) {
        $CI->load->library('app');
    }

    return $CI->app->get_option($name);
}
/**
 * Get option value from database
 * @param  string $name Option name
 * @return mixed
 */
function update_option($name, $value, $autoload = null)
{
    $CI =& get_instance();
    $CI->db->where('name', $name);
    $data = array(
        'value' => $value,
        );
    if ($autoload) {
        $data['autoload'] = $autoload;
    }
    $CI->db->update('tbloptions', $data);
    if ($CI->db->affected_rows() > 0) {
        return true;
    }

    return false;
}
/**
 * Delete option
 * @since  Version 1.0.4
 * @param  mixed $id option id
 * @return boolean
 */
function delete_option($id)
{
    $CI =& get_instance();
    $CI->db->where('id', $id);
    $CI->db->or_where('name', $id);
    $CI->db->delete('tbloptions');
    if ($CI->db->affected_rows() > 0) {
        return true;
    }

    return false;
}

/**
 * Get staff full name
 * @param  string $userid Optional
 * @return string Firstname and Lastname
 */
function get_staff_full_name($userid = '')
{
    $tmpStaffUserId = get_staff_user_id();
    if ($userid == "" || $userid == $tmpStaffUserId) {
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->firstname . ' ' . $GLOBALS['current_user']->lastname;
        }
        $userid = $tmpStaffUserId;
    }

    $CI =& get_instance();

    $staff =  $CI->object_cache->get('staff-full-name-data-'.$userid);

    if (!$staff) {
        $CI->db->where('staffid', $userid);
        $staff = $CI->db->select('firstname,lastname')->from('tblstaff')->get()->row();
        $CI->object_cache->add('staff-full-name-data-'.$userid, $staff);
    }

    return $staff ? $staff->firstname . ' ' . $staff->lastname : '';
}
/**
 * Get staff default language
 * @param  mixed $staffid
 * @return mixed
 */
function get_staff_default_language($staffid = '')
{
    if (!is_numeric($staffid)) {
        // checking for current user if is admin
        if (isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->default_language;
        }

        $staffid = get_staff_user_id();
    }
    $CI =& get_instance();
    $CI->db->select('default_language');
    $CI->db->from('tblstaff');
    $CI->db->where('staffid', $staffid);
    $staff = $CI->db->get()->row();
    if ($staff) {
        return $staff->default_language;
    }

    return '';
}
/**
 * Log Activity for everything
 * @param  string $description Activity Description
 * @param  integer $staffid    Who done this activity
 */
function logActivity($description, $staffid = null)
{
    $CI =& get_instance();
    $log = array(
        'description' => $description,
        'date' => date('Y-m-d H:i:s'),
        );
    if (!DEFINED('CRON')) {
        if ($staffid != null && is_numeric($staffid)) {
            $log['staffid'] = get_staff_full_name($staffid);
        } else {
            if (!is_client_logged_in()) {
                if (is_staff_logged_in()) {
                    $log['staffid'] = get_staff_full_name(get_staff_user_id());
                } else {
                    $log['staffid'] = null;
                }
            } else {
                $log['staffid'] = get_contact_full_name(get_contact_user_id());
            }
        }
    } else {
        // manually invoked cron
        if (is_staff_logged_in()) {
            $log['staffid'] = get_staff_full_name(get_staff_user_id());
        } else {
            $log['staffid'] = '[CRON]';
        }
    }

    $CI->db->insert('tblactivitylog', $log);
}
/**
 * Note well tested function do not use it, is optimized only when doing updates in the menu items
 */
function add_main_menu_item($options = array(), $parent = '')
{
    $default_options = array(
        'name',
        'permission',
        'icon',
        'url',
        'id',
        );
    $order           = '';
    if (isset($options['order'])) {
        $order = $options['order'];
        unset($options['order']);
    }
    $data = array();
    for ($i = 0; $i < count($default_options); $i++) {
        if (isset($options[$default_options[$i]])) {
            $data[$default_options[$i]] = $options[$default_options[$i]];
        } else {
            $data[$default_options[$i]] = '';
        }
    }
    $menu = get_option('aside_menu_active');
    $menu = json_decode($menu);
    // check if the id exists
    if ($data['id'] == '') {
        $data['id'] = slug_it($data['name']);
    }
    $total_exists = 0;
    foreach ($menu->aside_menu_active as $item) {
        if ($item->id == $data['id']) {
            $total_exists++;
        }
    }
    if ($total_exists > 0) {
        return false;
    }
    $_data = new stdClass();
    foreach ($data as $key => $val) {
        $_data->{$key} = $val;
    }

    $data = $_data;
    if ($parent == '') {
        if ($order == '') {
            array_push($menu->aside_menu_active, $data);
        } else {
            if ($order == 1) {
                array_unshift($menu->aside_menu_active, array());
            } else {
                $order = $order - 1;
                array_splice($menu->aside_menu_active, $order, 0, array(
                    '',
                    ));
            }
            $menu->aside_menu_active[$order] = $_data;
        }
    } else {
        $i = 0;
        $parent_found = false;
        foreach ($menu->aside_menu_active as $item) {
            if ($item->id == $parent) {
                $parent_found = true;
                if (!isset($item->children)) {
                    $menu->aside_menu_active[$i]->children   = array();
                    $menu->aside_menu_active[$i]->children[] = $data;
                    break;
                } else {
                    if ($order == '') {
                        $menu->aside_menu_active[$i]->children[] = $data;
                    } else {
                        if ($order == 1) {
                            array_unshift($menu->aside_menu_active[$i]->children, array());
                        } else {
                            $order = $order - 1;
                            array_splice($menu->aside_menu_active[$i]->children, $order, 0, array(
                                '',
                                ));
                        }
                        $menu->aside_menu_active[$i]->children[$order] = $data;
                    }
                    break;
                }
            }
            $i++;
        }
        if ($parent_found == false) {
            $data = (array) $data;
            add_main_menu_item($data);

            return true;
        }
    }
    if (update_option('aside_menu_active', json_encode($menu))) {
        return true;
    }

    return false;
}
/**
 * Note well tested function do not use it, is optimized only when doing updates in the menu items
 */
function add_setup_menu_item($options = array(), $parent = '')
{
    $default_options = array(
        'name',
        'permission',
        'icon',
        'url',
        'id',
        );
    $order           = '';
    if (isset($options['order'])) {
        $order = $options['order'];
        unset($options['order']);
    }
    $data = array();
    for ($i = 0; $i < count($default_options); $i++) {
        if (isset($options[$default_options[$i]])) {
            $data[$default_options[$i]] = $options[$default_options[$i]];
        } else {
            $data[$default_options[$i]] = '';
        }
    }
    if ($data['id'] == '') {
        $data['id'] = slug_it($data['name']);
    }

    $menu = get_option('setup_menu_active');
    $menu = json_decode($menu);
    // check if the id exists
    if ($data['id'] == '') {
        $data['id'] = slug_it($data['name']);
    }
    $total_exists = 0;
    foreach ($menu->setup_menu_active as $item) {
        if ($item->id == $data['id']) {
            $total_exists++;
        }
    }
    if ($total_exists > 0) {
        return false;
    }
    $_data = new stdClass();
    foreach ($data as $key => $val) {
        $_data->{$key} = $val;
    }
    $data = $_data;
    if ($parent == '') {
        if ($order == 1) {
            array_unshift($menu->setup_menu_active, array());
        } else {
            $order = $order - 1;
            array_splice($menu->setup_menu_active, $order, 0, array(
                '',
                ));
        }
        $menu->setup_menu_active[$order] = $_data;
    } else {
        $i = 0;
        foreach ($menu->setup_menu_active as $item) {
            if ($item->id == $parent) {
                if (!isset($item->children)) {
                    $menu->setup_menu_active[$i]->children   = array();
                    $menu->setup_menu_active[$i]->children[] = $data;
                    break;
                } else {
                    $menu->setup_menu_active[$i]->children[] = $data;
                    break;
                }
            }
            $i++;
        }
    }
    if (update_option('setup_menu_active', json_encode($menu))) {
        return true;
    }

    return false;
}
/**
 * Add user notifications
 * @param array $values array of values [description,fromuserid,touserid,fromcompany,isread]
 */
function add_notification($values)
{
    $CI =& get_instance();
    foreach ($values as $key => $value) {
        $data[$key] = $value;
    }
    if (is_client_logged_in()) {
        $data['fromuserid']    = 0;
        $data['fromclientid']  = get_contact_user_id();
        $data['from_fullname'] = get_contact_full_name(get_contact_user_id());
    } else {
        $data['fromuserid']    = get_staff_user_id();
        $data['fromclientid']  = 0;
        $data['from_fullname'] = get_staff_full_name(get_staff_user_id());
    }

    if (isset($data['fromcompany'])) {
        unset($data['fromuserid']);
        unset($data['from_fullname']);
    }

    // Prevent sending notification to non active users.
    if (isset($data['touserid']) && $data['touserid'] != 0) {
        $CI->db->where('staffid', $data['touserid']);
        $user = $CI->db->get('tblstaff')->row();
        if (!$user) {
            return false;
        }
        if ($user) {
            if ($user->active == 0) {
                return false;
            }
        }
    }
    $data['date'] = date('Y-m-d H:i:s');

    $CI->db->insert('tblnotifications', $data);

    return true;
}
/**
 * Count total rows on table based on params
 * @param  string $table Table from where to count
 * @param  array  $where
 * @return mixed  Total rows
 */
function total_rows($table, $where = array())
{
    $CI =& get_instance();
    if (is_array($where)) {
        if (sizeof($where) > 0) {
            $CI->db->where($where);
        }
    } elseif (strlen($where) > 0) {
        $CI->db->where($where);
    }

    return $CI->db->count_all_results($table);
}
/**
 * Sum total from table
 * @param  string $table table name
 * @param  array  $attr  attributes
 * @return mixed
 */
function sum_from_table($table, $attr = array())
{
    if (!isset($attr['field'])) {
        show_error('sum_from_table(); function expect field to be passed.');
    }

    $CI =& get_instance();
    if (isset($attr['where']) && is_array($attr['where'])) {
        $i = 0;
        foreach ($attr['where'] as $key => $val) {
            if (is_numeric($key)) {
                $CI->db->where($val);
                unset($attr['where'][$key]);
            }
            $i++;
        }
        $CI->db->where($attr['where']);
    }
    $CI->db->select_sum($attr['field']);
    $CI->db->from($table);
    $result = $CI->db->get()->row();

    return $result->{$attr['field']};
}

/**
 * Prefix field name with table ex. table.column
 * @param  string $table
 * @param  string $alias
 * @param  string $field field to check
 * @return string
 */
function prefixed_table_fields_wildcard($table, $alias, $field)
{
    $CI =& get_instance();
    $columns     = $CI->db->query("SHOW COLUMNS FROM $table")->result_array();
    $field_names = array();
    foreach ($columns as $column) {
        $field_names[] = $column["Field"];
    }
    $prefixed = array();
    foreach ($field_names as $field_name) {
        if ($field == $field_name) {
            $prefixed[] = "`{$alias}`.`{$field_name}` AS `{$alias}.{$field_name}`";
        }
    }

    return implode(", ", $prefixed);
}
/**
 * Prefix all columns from table with the table name
 * Used for select statements eq tblclients.company
 * @param  string $table table name
 * @param  array $exclude exclude fields from prefixing
 * @return array
 */
function prefixed_table_fields_array($table, $string = false, $exclude = array())
{
    $CI =& get_instance();
    $fields = $CI->db->list_fields($table);

    foreach ($exclude as $field) {
        if (in_array($field, $fields)) {
            unset($fields[array_search($field, $fields)]);
        }
    }

    $fields = array_values($fields);

    $i      = 0;
    foreach ($fields as $f) {
        $fields[$i] = $table . '.' . $f;
        $i++;
    }

    return $string == false ? $fields : implode(',', $fields);
}

/**
 * Prefix all columns from table with the table name
 * Used for select statements eq tblclients.company
 * @param  string $table table name
 * @param  array $exclude exclude fields from prefixing
 * @return string
 */
function prefixed_table_fields_string($table, $exclude = array())
{
    return prefixed_table_fields_array($table, true, $exclude);
}
/**
 * Helper function to get all knowledge base groups in the parents groups
 * @param  boolean $only_customers prevent showing internal kb articles in customers area
 * @param  array   $where
 * @return array
 */
function get_all_knowledge_base_articles_grouped($only_customers = true, $where = array())
{
    $CI =& get_instance();
    $CI->load->model('knowledge_base_model');
    $groups = $CI->knowledge_base_model->get_kbg('', 1);
    $i      = 0;
    foreach ($groups as $group) {
        $CI->db->select('slug,subject,description,tblknowledgebase.active as active_article,articlegroup,articleid,staff_article');
        $CI->db->from('tblknowledgebase');
        $CI->db->where('articlegroup', $group['groupid']);
        $CI->db->where('active', 1);
        if ($only_customers == true) {
            $CI->db->where('staff_article', 0);
        }
        $CI->db->where($where);
        $CI->db->order_by('article_order', 'asc');
        $articles = $CI->db->get()->result_array();
        if (count($articles) == 0) {
            unset($groups[$i]);
            $i++;
            continue;
        }
        $groups[$i]['articles'] = $articles;
        $i++;
    }

    return $groups;
}
/**
 * Helper function to get all announcements for user
 * @param  boolean $staff Is this client or staff
 * @return array
 */
function get_announcements_for_user($staff = true)
{
    if (!is_logged_in()) {
        return array();
    }

    $CI =& get_instance();
    $CI->db->select();

    if ($staff == true) {
        $CI->db->where('announcementid NOT IN (SELECT announcementid FROM tbldismissedannouncements WHERE staff=1 AND userid = ' . get_staff_user_id() . ') AND showtostaff = 1');
    } else {
        $contact_id = get_contact_user_id();
        if (!is_client_logged_in()) {
            return array();
        }

        if ($contact_id) {
            $CI->db->where('announcementid NOT IN (SELECT announcementid FROM tbldismissedannouncements WHERE staff=0 AND userid = ' . $contact_id . ') AND showtousers = 1');
        } else {
            return array();
        }
    }
    $CI->db->order_by('dateadded','desc');
    $announcements = $CI->db->get('tblannouncements');
    if ($announcements) {
        return $announcements->result_array();
    } else {
        return array();
    }
}
/**
 * Helper function to get text question answers
 * @param  integer $questionid
 * @param  itneger $surveyid
 * @return array
 */
function get_text_question_answers($questionid, $surveyid)
{
    $CI =& get_instance();
    $CI->db->select('answer,resultid');
    $CI->db->from('tblformresults');
    $CI->db->where('questionid', $questionid);
    $CI->db->where('rel_id', $surveyid);
    $CI->db->where('rel_type', 'survey');

    return $CI->db->get()->result_array();
}

/**
 * Get department email address
 * @param  mixed $id department id
 * @return mixed
 */
function get_department_email($id)
{
    $CI =& get_instance();
    $CI->db->where('departmentid', $id);

    return $CI->db->get('tbldepartments')->row()->email;
}
/**
 * Helper function to get all knowledbase groups
 * @return array
 */
function get_kb_groups()
{
    $CI =& get_instance();

    return $CI->db->get('tblknowledgebasegroups')->result_array();
}
/**
 * Get all countries stored in database
 * @return array
 */
function get_all_countries()
{
    $CI =& get_instance();

    return $CI->db->get('tblcountries')->result_array();
}
/**
 * Get country row from database based on passed country id
 * @param  mixed $id
 * @return object
 */
function get_country($id)
{
    $CI =& get_instance();

    $country = $CI->object_cache->get('db-country-'.$id);

    if (!$country) {
        $CI->db->where('country_id', $id);
        $country = $CI->db->get('tblcountries')->row();
        $CI->object_cache->add('db-country-'.$id, $country);
    }

    return $country;
}
/**
 * Get country short name by passed id
 * @param  mixed $id county id
 * @return mixed
 */
function get_country_short_name($id)
{
    $CI =& get_instance();
    $country = get_country($id);
    if ($country) {
        return $country->iso2;
    }

    return '';
}
/**
 * Get country name by passed id
 * @param  mixed $id county id
 * @return mixed
 */
function get_country_name($id)
{
    $CI =& get_instance();
    $country = get_country($id);
    if ($country) {
        return $country->short_name;
    }

    return '';
}
