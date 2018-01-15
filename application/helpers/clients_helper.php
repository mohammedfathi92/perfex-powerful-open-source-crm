<?php

/**
 * Check if client id is used in the system
 * @param  mixed  $id client id
 * @return boolean
 */
function is_client_id_used($id)
{
    $total = 0;

    $total += total_rows('tblcontracts', array(
        'client' => $id,
    ));

    $total += total_rows('tblestimates', array(
        'clientid' => $id,
    ));

    $total += total_rows('tblexpenses', array(
        'clientid' => $id,
    ));

    $total += total_rows('tblinvoices', array(
        'clientid' => $id,
    ));

    $total += total_rows('tblproposals', array(
        'rel_id' => $id,
        'rel_type' => 'customer',
    ));

    $total += total_rows('tbltickets', array(
        'userid' => $id,
    ));

    $total += total_rows('tblprojects', array(
        'clientid' => $id,
    ));

    $total += total_rows('tblstafftasks', array(
        'rel_id' => $id,
        'rel_type' => 'customer',
    ));

    $total += total_rows('tblcreditnotes', array(
        'clientid' => $id,
    ));

    if ($total > 0) {
        return true;
    }

    return false;
}

/**
 * Get predefined tabs array, used in customer profile
 * @param  mixed $customer_id customer id to prepare the urls
 * @return array
 */
function get_customer_profile_tabs($customer_id)
{
    $customer_tabs = array(
  array(
    'name'=>'profile',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=profile'),
    'icon'=>'fa fa-user-circle',
    'lang'=>_l('client_add_edit_profile'),
    'visible'=>true,
    'order'=>1,
    ),
  array(
    'name'=>'notes',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=notes'),
    'icon'=>'fa fa-sticky-note-o',
    'lang'=>_l('contracts_notes_tab'),
    'visible'=>true,
    'order'=>2,
    ),
  array(
    'name'=>'statement',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=statement'),
    'icon'=>'fa fa-area-chart',
    'lang'=>_l('customer_statement'),
    'visible'=>(has_permission('invoices', '', 'view') && has_permission('payments', '', 'view')),
    'order'=>3,
    ),
  array(
    'name'=>'invoices',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=invoices'),
    'icon'=>'fa fa-file-text',
    'lang'=>_l('client_invoices_tab'),
    'visible'=>(has_permission('invoices', '', 'view') || has_permission('invoices', '', 'view_own')),
    'order'=>4,
    ),
  array(
    'name'=>'payments',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=payments'),
    'icon'=>'fa fa-line-chart',
    'lang'=>_l('client_payments_tab'),
    'visible'=>(has_permission('payments', '', 'view') || has_permission('invoices', '', 'view_own')),
    'order'=>5,
    ),
  array(
    'name'=>'proposals',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=proposals'),
    'icon'=>'fa fa-file-powerpoint-o',
    'lang'=>_l('proposals'),
    'visible'=>(has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || (get_option('allow_staff_view_proposals_assigned') == 1 && total_rows('tblproposals', array('assigned'=>get_staff_user_id())) > 0)),
    'order'=>6,
    ),
    array(
    'name'=>'credit_notes',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=credit_notes'),
    'icon'=>'fa fa-sticky-note-o',
    'lang'=>_l('credit_notes'),
    'visible'=>(has_permission('credit_notes', '', 'view') || has_permission('credit_notes', '', 'view_own')),
    'order'=>7,
    ),
  array(
    'name'=>'estimates',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=estimates'),
    'icon'=>'fa fa-clipboard',
    'lang'=>_l('estimates'),
    'visible'=>(has_permission('estimates', '', 'view') || has_permission('estimates', '', 'view_own')),
    'order'=>8,
    ),
  array(
    'name'=>'expenses',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=expenses'),
    'icon'=>'fa fa-file-text-o',
    'lang'=>_l('expenses'),
    'visible'=>(has_permission('expenses', '', 'view') || has_permission('expenses', '', 'view_own')),
    'order'=>9,
    ),
  array(
    'name'=>'contracts',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=contracts'),
    'icon'=>'fa fa-file',
    'lang'=>_l('contracts'),
    'visible'=>(has_permission('contracts', '', 'view') || has_permission('contracts', '', 'view_own')),
    'order'=>10,
    ),
  array(
    'name'=>'projects',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=projects'),
    'icon'=>'fa fa-bars',
    'lang'=>_l('projects'),
    'visible'=>true,
    'order'=>11,
    ),
    array(
    'name'=>'tasks',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=tasks'),
    'icon'=>'fa fa-tasks',
    'lang'=>_l('tasks'),
    'visible'=>true,
    'order'=>12,
    ),
  array(
    'name'=>'tickets',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=tickets'),
    'icon'=>'fa fa-ticket',
    'lang'=>_l('tickets'),
    'visible'=>((get_option('access_tickets_to_none_staff_members') == 1 && !is_staff_member()) || is_staff_member()),
    'order'=>13,
    ),
  array(
    'name'=>'attachments',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=attachments'),
    'icon'=>'fa fa-paperclip',
    'lang'=>_l('customer_attachments'),
    'visible'=>true,
    'order'=>14,
    ),
  array(
    'name'=>'vault',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=vault'),
    'icon'=>'fa fa-lock',
    'lang'=>_l('vault'),
    'visible'=>true,
    'order'=>15,
    ),
  array(
    'name'=>'reminders',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=reminders'),
    'icon'=>'fa fa-clock-o',
    'lang'=>_l('client_reminders_tab'),
    'visible'=>true,
    'order'=>16,
    'id'=>'reminders',
    ),
  array(
    'name'=>'map',
    'url'=>admin_url('clients/client/'.$customer_id.'?group=map'),
    'icon'=>'fa fa-map-marker',
    'lang'=>_l('customer_map'),
    'visible'=>true,
    'order'=>17,
    ),

  );

    $hook_data = do_action('customer_profile_tabs', array('tabs'=>$customer_tabs, 'customer_id'=>$customer_id));
    $customer_tabs = $hook_data['tabs'];

    usort($customer_tabs, function ($a, $b) {
        return $a['order'] - $b['order'];
    });

    return $customer_tabs;
}

/**
 * Get client id by lead id
 * @since  Version 1.0.1
 * @param  mixed $id lead id
 * @return mixed     client id
 */
function get_client_id_by_lead_id($id)
{
    $CI =& get_instance();
    $CI->db->select('userid')->from('tblclients')->where('leadid', $id);

    return $CI->db->get()->row()->userid;
}

/**
 * Check if contact id passed is primary contact
 * If you dont pass $contact_id the current logged in contact will be checked
 * @param  string  $contact_id
 * @return boolean
 */
function is_primary_contact($contact_id = '')
{
    if (!is_numeric($contact_id)) {
        $contact_id = get_contact_user_id();
    }

    if (total_rows('tblcontacts', array(
        'id' => $contact_id,
        'is_primary' => 1,
        )) > 0) {
        return true;
    }

    return false;
}

/**
 * Check if client have invoices with multiple currencies
 * @return booelan
 */
function is_client_using_multiple_currencies($clientid = '', $table = 'tblinvoices')
{
    $CI =& get_instance();

    $clientid = $clientid == '' ? get_client_user_id() : $clientid;
    $CI->load->model('currencies_model');
    $currencies            = $CI->currencies_model->get();
    $total_currencies_used = 0;
    foreach ($currencies as $currency) {
        $CI->db->where('currency', $currency['id']);
        $CI->db->where('clientid', $clientid);
        $total = $CI->db->count_all_results($table);
        if ($total > 0) {
            $total_currencies_used++;
        }
    }
    if ($total_currencies_used > 1) {
        return true;
    } elseif ($total_currencies_used == 0 || $total_currencies_used == 1) {
        return false;
    }

    return true;
}


/**
 * Function used to check if is really empty customer company
 * Can happen user to have selected that the company field is not required and the primary contact name is auto added in the company field
 * @param  mixed  $id
 * @return boolean
 */
function is_empty_customer_company($id)
{
    $CI =& get_instance();
    $CI->db->select('company');
    $CI->db->from('tblclients');
    $CI->db->where('userid', $id);
    $row = $CI->db->get()->row();
    if ($row) {
        if ($row->company == '') {
            return true;
        }

        return false;
    }

    return true;
}

/**
 * Get ids to check what files with contacts are shared
 * @param  array  $where
 * @return array
 */
function get_customer_profile_file_sharing($where = array())
{
    $CI =& get_instance();
    $CI->db->where($where);

    return $CI->db->get('tblcustomerfiles_shares')->result_array();
}

/**
 * Get customer id by passed contact id
 * @param  mixed $id
 * @return mixed
 */
function get_user_id_by_contact_id($id)
{
    $CI =& get_instance();
    $CI->db->select('userid');
    $CI->db->where('id', $id);
    $client = $CI->db->get('tblcontacts')->row();
    if ($client) {
        return $client->userid;
    }

    return false;
}

/**
 * Get primary contact user id for specific customer
 * @param  mixed $userid
 * @return mixed
 */
function get_primary_contact_user_id($userid)
{
    $CI =& get_instance();
    $CI->db->where('userid', $userid);
    $CI->db->where('is_primary', 1);
    $row = $CI->db->get('tblcontacts')->row();

    if ($row) {
        return $row->id;
    }

    return false;
}

/**
 * Get client full name
 * @param  string $contact_id Optional
 * @return string Firstname and Lastname
 */
function get_contact_full_name($contact_id = '')
{
    $contact_id == '' ? get_contact_user_id() : $contact_id;

    $CI =& get_instance();

    $contact = $CI->object_cache->get('contact-full-name-data-'.$contact_id);

    if(!$contact){
        $CI->db->where('id', $contact_id);
        $contact = $CI->db->select('firstname,lastname')->from('tblcontacts')->get()->row();
        $CI->object_cache->add('contact-full-name-data-'.$contact_id, $contact);
    }
    if ($contact) {
        return $contact->firstname . ' ' . $contact->lastname;
    } else {
        return '';
    }
}
/**
 * Return contact profile image url
 * @param  mixed $contact_id
 * @param  string $type
 * @return string
 */
function contact_profile_image_url($contact_id, $type = 'small')
{
    $url = base_url('assets/images/user-placeholder.jpg');
    $CI =& get_instance();
    $CI->db->select('profile_image');
    $CI->db->from('tblcontacts');
    $CI->db->where('id', $contact_id);
    $contact = $CI->db->get()->row();
    if ($contact) {
        if (!empty($contact->profile_image)) {
            $path = 'uploads/client_profile_images/' . $contact_id . '/' . $type . '_' . $contact->profile_image;
            if (file_exists($path)) {
                $url = base_url($path);
            }
        }
    }

    return $url;
}
/**
 * Used in:
 * Search contact tickets
 * Project dropdown quick switch
 * Calendar tooltips
 * @param  [type] $userid [description]
 * @return [type]         [description]
 */
function get_company_name($userid, $prevent_empty_company = false)
{
    $_userid = get_client_user_id();
    if ($userid !== '') {
        $_userid = $userid;
    }
    $CI =& get_instance();

    $select = ($prevent_empty_company == false ? get_sql_select_client_company() : 'company');

    $client = $CI->db->select($select)
    ->where('userid', $_userid)
    ->from('tblclients')
    ->get()
    ->row();
    if ($client) {
        return $client->company;
    } else {
        return '';
    }
}


/**
 * Get client default language
 * @param  mixed $clientid
 * @return mixed
 */
function get_client_default_language($clientid = '')
{
    if (!is_numeric($clientid)) {
        $clientid = get_client_user_id();
    }
    $CI =& get_instance();
    $CI->db->select('default_language');
    $CI->db->from('tblclients');
    $CI->db->where('userid', $clientid);
    $client = $CI->db->get()->row();
    if ($client) {
        return $client->default_language;
    }

    return '';
}

/**
 * Function is customer admin
 * @param  mixed  $id       customer id
 * @param  staff_id  $staff_id staff id to check
 * @return boolean
 */
function is_customer_admin($id, $staff_id = '')
{
    return total_rows('tblcustomeradmins', array(
            'customer_id' => $id,
            'staff_id' => is_numeric($staff_id) ? $staff_id : get_staff_user_id(),
    )) > 0 ? true : false;
}
/**
 * Check if staff member have assigned customers
 * @param  mixed $staff_id staff id
 * @return boolean
 */
function have_assigned_customers($staff_id = '')
{
    return total_rows('tblcustomeradmins', array(
        'staff_id' => is_numeric($staff_id) ? $staff_id : get_staff_user_id(),
    )) > 0 ? true : false;
}
/**
 * Check if contact has permission
 * @param  string  $permission permission name
 * @param  string  $contact_id     contact id
 * @return boolean
 */
function has_contact_permission($permission, $contact_id = '')
{
    $CI =& get_instance();
    if (!class_exists('perfex_base')) {
        $CI->load->library('perfex_base');
    }
    $permissions = $CI->perfex_base->get_contact_permissions();
    // Contact id passed form function
    if ($contact_id != '') {
        $_contact_id = $contact_id;
    } else {
        // Current logged in contact
        $_contact_id     = get_contact_user_id();
    }
    foreach ($permissions as $_permission) {
        if ($_permission['short_name'] == $permission) {
            if (total_rows('tblcontactpermissions', array(
                'permission_id' => $_permission['id'],
                'userid' => $_contact_id,
            )) > 0) {
                return true;
            }
        }
    }

    return false;
}
/**
 * Load customers area language
 * @param  string $customer_id
 * @return string return loaded language
 */
function load_client_language($customer_id = '')
{
    $CI =& get_instance();
    $language = get_option('active_language');
    if (is_client_logged_in() || $customer_id != '') {
        $client_language = get_client_default_language($customer_id);
        if (!empty($client_language)) {
            if (file_exists(APPPATH . 'language/' . $client_language)) {
                $language = $client_language;
            }
        }
    }

    $CI->lang->load($language . '_lang', $language);
    if (file_exists(APPPATH . 'language/' . $language . '/custom_lang.php')) {
        $CI->lang->load('custom_lang', $language);
    }

    $language = do_action('after_load_client_language', $language);

    return $language;
}
/**
 * Check if client have transactions recorded
 * @param  mixed $id clientid
 * @return boolean
 */
function client_have_transactions($id)
{
    $total_transactions = 0;

    $total_transactions += total_rows('tblinvoices', array(
        'clientid' => $id,
        ));

    $total_transactions += total_rows('tblcreditnotes', array(
        'clientid' => $id,
        ));

    $total_transactions += total_rows('tblestimates', array(
        'clientid' => $id,
        ));

    $total_transactions += total_rows('tblexpenses', array(
        'clientid' => $id,
        'billable' => 1,
        ));

    $total_transactions += total_rows('tblproposals', array(
        'rel_id' => $id,
        'rel_type' => 'customer',
        ));

    if ($total_transactions > 0) {
        return true;
    }

    return false;
}

/**
 * Additional checking for customers area, when contact edit his profile
 * This function will check if the checkboxes for email notifications should be shown
 * @return boolean
 */
function can_contact_view_email_notifications_options()
{
    if (has_contact_permission('invoices') || has_contact_permission('estimates') || has_contact_permission('projects') || has_contact_permission('contracts')) {
        return true;
    }

    return false;
}

/**
* With this function staff can login as client in the clients area
* @param  mixed $id client id
*/
function login_as_client($id)
{
    $CI = &get_instance();

    $CI->db->select('tblcontacts.id')
    ->where('userid', $id)
    ->where('is_primary', 1);

    $primary = $CI->db->get('tblcontacts')->row();

    if (!$primary) {
        set_alert('danger', _l('no_primary_contact'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    $user_data = array(
            'client_user_id' => $id,
            'contact_user_id' => $primary->id,
            'client_logged_in' => true,
            'logged_in_as_client' => true,
        );

    $CI->session->set_userdata($user_data);
}

/**
*  Get customer attachment
* @param   mixed $id   customer id
* @return  array
*/
function get_all_customer_attachments($id)
{
    $CI = &get_instance();

    $attachments             = array();
    $attachments['invoice']  = array();
    $attachments['estimate'] = array();
    $attachments['credit_note'] = array();
    $attachments['proposal'] = array();
    $attachments['contract'] = array();
    $attachments['lead']     = array();
    $attachments['task']     = array();
    $attachments['customer'] = array();
    $attachments['ticket']   = array();
    $attachments['expense']  = array();
    $has_permission_expenses_view = has_permission('expenses', '', 'view');
    $has_permission_expenses_own  = has_permission('expenses', '', 'view_own');
    if ($has_permission_expenses_view || $has_permission_expenses_own) {
        // Expenses
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);
        if (!$has_permission_expenses_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }
        $CI->db->from('tblexpenses');
        $expenses = $CI->db->get()->result_array();
        foreach ($expenses as $expense) {
            $CI->db->where('rel_id', $expense['id']);
            $CI->db->where('rel_type', 'expense');
            $_attachments = $CI->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['expense'], $_att);
                }
            }
        }
    }


    $has_permission_invoices_view = has_permission('invoices', '', 'view');
    $has_permission_invoices_own  = has_permission('invoices', '', 'view_own');
    if ($has_permission_invoices_view || $has_permission_invoices_own) {
        // Invoices
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);

        if (!$has_permission_invoices_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }

        $CI->db->from('tblinvoices');
        $invoices = $CI->db->get()->result_array();
        foreach ($invoices as $invoice) {
            $CI->db->where('rel_id', $invoice['id']);
            $CI->db->where('rel_type', 'invoice');
            $_attachments = $CI->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['invoice'], $_att);
                }
            }
        }
    }

    $has_permission_credit_notes_view = has_permission('credit_notes', '', 'view');
    $has_permission_credit_notes_own  = has_permission('credit_notes', '', 'view_own');
    if ($has_permission_credit_notes_view || $has_permission_credit_notes_own) {
        // credit_notes
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);

        if (!$has_permission_credit_notes_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }

        $CI->db->from('tblcreditnotes');
        $credit_notes = $CI->db->get()->result_array();
        foreach ($credit_notes as $credit_note) {
            $CI->db->where('rel_id', $credit_note['id']);
            $CI->db->where('rel_type', 'credit_note');
            $_attachments = $CI->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['credit_note'], $_att);
                }
            }
        }
    }

    $permission_estimates_view = has_permission('estimates', '', 'view');
    $permission_estimates_own  = has_permission('estimates', '', 'view_own');

    if ($permission_estimates_view || $permission_estimates_own) {
        // Estimates
        $CI->db->select('clientid,id');
        $CI->db->where('clientid', $id);
        if (!$permission_estimates_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }
        $CI->db->from('tblestimates');
        $estimates = $CI->db->get()->result_array();
        foreach ($estimates as $estimate) {
            $CI->db->where('rel_id', $estimate['id']);
            $CI->db->where('rel_type', 'estimate');
            $_attachments = $CI->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['estimate'], $_att);
                }
            }
        }
    }

    $has_permission_proposals_view = has_permission('proposals', '', 'view');
    $has_permission_proposals_own  = has_permission('proposals', '', 'view_own');

    if ($has_permission_proposals_view || $has_permission_proposals_own) {
        // Proposals
        $CI->db->select('rel_id,id');
        $CI->db->where('rel_id', $id);
        $CI->db->where('rel_type', 'customer');
        if (!$has_permission_proposals_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }
        $CI->db->from('tblproposals');
        $proposals = $CI->db->get()->result_array();
        foreach ($proposals as $proposal) {
            $CI->db->where('rel_id', $proposal['id']);
            $CI->db->where('rel_type', 'proposal');
            $_attachments = $CI->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['proposal'], $_att);
                }
            }
        }
    }

    $permission_contracts_view = has_permission('contracts', '', 'view');
    $permission_contracts_own  = has_permission('contracts', '', 'view_own');
    if ($permission_contracts_view || $permission_contracts_own) {
        // Contracts
        $CI->db->select('client,id');
        $CI->db->where('client', $id);
        if (!$permission_contracts_view) {
            $CI->db->where('addedfrom', get_staff_user_id());
        }
        $CI->db->from('tblcontracts');
        $contracts = $CI->db->get()->result_array();
        foreach ($contracts as $contract) {
            $CI->db->where('rel_id', $contract['id']);
            $CI->db->where('rel_type', 'contract');
            $_attachments = $CI->db->get('tblfiles')->result_array();
            if (count($_attachments) > 0) {
                foreach ($_attachments as $_att) {
                    array_push($attachments['contract'], $_att);
                }
            }
        }
    }

    $CI->db->select('leadid')
    ->where('userid', $id);
    $customer = $CI->db->get('tblclients')->row();

    if ($customer->leadid != null) {
        $CI->db->where('rel_id', $customer->leadid);
        $CI->db->where('rel_type', 'lead');
        $_attachments = $CI->db->get('tblfiles')->result_array();
        if (count($_attachments) > 0) {
            foreach ($_attachments as $_att) {
                array_push($attachments['lead'], $_att);
            }
        }
    }
    $CI->db->select('ticketid,userid');
    $CI->db->where('userid', $id);
    $CI->db->from('tbltickets');
    $tickets = $CI->db->get()->result_array();
    foreach ($tickets as $ticket) {
        $CI->db->where('ticketid', $ticket['ticketid']);
        $_attachments = $CI->db->get('tblticketattachments')->result_array();
        if (count($_attachments) > 0) {
            foreach ($_attachments as $_att) {
                array_push($attachments['ticket'], $_att);
            }
        }
    }

    $has_permission_tasks_view = has_permission('tasks', '', 'view');
    $CI->db->select('rel_id,id');
    $CI->db->where('rel_id', $id);
    $CI->db->where('rel_type', 'customer');

    if (!$has_permission_tasks_view) {
        $CI->db->where(get_tasks_where_string(false));
    }

    $CI->db->from('tblstafftasks');
    $tasks = $CI->db->get()->result_array();
    foreach ($tasks as $task) {
        $CI->db->where('rel_type', 'task');
        $CI->db->where('rel_id', $task['id']);
        $_attachments = $CI->db->get('tblfiles')->result_array();
        if (count($_attachments) > 0) {
            foreach ($_attachments as $_att) {
                array_push($attachments['task'], $_att);
            }
        }
    }

    $CI->db->where('rel_id', $id);
    $CI->db->where('rel_type', 'customer');
    $client_main_attachments = $CI->db->get('tblfiles')->result_array();

    $attachments['customer'] = $client_main_attachments;

    return $attachments;
}



add_action('check_vault_entries_visibility', '_check_vault_entries_visibility');

/**
 * Used in customer profile vaults feature to determine if the vault should be shown for staff
 * @param  array $entries vault entries from database
 * @return array
 */
function _check_vault_entries_visibility($entries)
{
    $new = array();
    foreach ($entries as $entry) {
        if ($entry['visibility'] != 1) {
            if ($entry['visibility'] == 2 && !is_admin() && $entry['creator'] != get_staff_user_id()) {
                continue;
            } elseif ($entry['visibility'] == 3 && $entry['creator'] != get_staff_user_id() && !is_admin()) {
                continue;
            }
        }
        $new[] = $entry;
    }
    if (count($new) == 0) {
        $new = -1;
    }

    return $new;
}
