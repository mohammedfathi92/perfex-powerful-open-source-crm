<?php

/**
 * Function used to get related data based on rel_id and rel_type
 * Eq in the tasks section there is field where this task is related eq invoice with number INV-0005
 * @param  string $type
 * @param  string $rel_id
 * @param  string $connection_type
 * @param  string $connection_id
 * @return mixed
 */
function get_relation_data($type, $rel_id = '', $connection_type = '', $connection_id = '')
{
    $CI =& get_instance();
    $q = '';
    if ($CI->input->post('q')) {
        $q = $CI->input->post('q');
        $q = trim($q);
    }
    $data = array();
    if ($type == 'customer' || $type == 'customers') {
        $where_clients = 'tblclients.active=1';
        if ($connection_id != '') {
            if ($connection_type == 'proposal') {
                $where_clients = 'CASE
                WHEN tblclients.userid NOT IN(SELECT rel_id FROM tblproposals WHERE id=' . $connection_id . ' AND rel_type="customer") THEN tblclients.active=1
                ELSE 1=1
                END';
            } elseif ($connection_type == 'task') {
                $where_clients = 'CASE
                WHEN tblclients.userid NOT IN(SELECT rel_id FROM tblstafftasks WHERE id=' . $connection_id . ' AND rel_type="customer") THEN tblclients.active=1
                ELSE 1=1
                END';
            }
        }

        if ($q) {
            $where_clients .= ' AND (company LIKE "%' . $q . '%" OR CONCAT(firstname, " ", lastname) LIKE "%' . $q . '%" OR email LIKE "%' . $q . '%")';
        }
        $data = $CI->clients_model->get($rel_id, $where_clients);
    } elseif ($type == 'contact' || $type == 'contacts') {
        if ($rel_id != '') {
            $data = $CI->clients_model->get_contact($rel_id);
        } else {
            $where_contacts = 'tblcontacts.active=1';
            if ($CI->input->post('tickets_contacts')) {
                if (!has_permission('customers', '', 'view') && get_option('staff_members_open_tickets_to_all_contacts') == 0) {
                    $where_contacts .= ' AND tblcontacts.userid IN (SELECT customer_id FROM tblcustomeradmins WHERE staff_id=' . get_staff_user_id() . ')';
                }
            }
            if ($CI->input->post('contact_userid')) {
                $where_contacts .= ' AND tblcontacts.userid='.$CI->input->post('contact_userid');
            }
            $search = $CI->misc_model->_search_contacts($q, 0, $where_contacts);
            $data   = $search['result'];
        }
    } elseif ($type == 'invoice') {
        if ($rel_id != '') {
            $CI->load->model('invoices_model');
            $data = $CI->invoices_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_invoices($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'credit_note') {
        if ($rel_id != '') {
            $CI->load->model('credit_notes_model');
            $data = $CI->credit_notes_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_credit_notes($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'estimate') {
        if ($rel_id != '') {
            $CI->load->model('estimates_model');
            $data = $CI->estimates_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_estimates($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'contract' || $type == 'contracts') {
        $CI->load->model('contracts_model');

        if ($rel_id != '') {
            $CI->load->model('contracts_model');
            $data = $CI->contracts_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_contracts($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'ticket') {
        if ($rel_id != '') {
            $CI->load->model('tickets_model');
            $data = $CI->tickets_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_tickets($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'expense' || $type == 'expenses') {
        if ($rel_id != '') {
            $CI->load->model('expenses_model');
            $data = $CI->expenses_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_expenses($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'lead' || $type == 'leads') {
        if ($rel_id != '') {
            $CI->load->model('leads_model');
            $data = $CI->leads_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_leads($q, 0, array(
                'junk' => 0,
                ));
            $data   = $search['result'];
        }
    } elseif ($type == 'proposal') {
        if ($rel_id != '') {
            $CI->load->model('proposals_model');
            $data = $CI->proposals_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_proposals($q);
            $data   = $search['result'];
        }
    } elseif ($type == 'project') {
        if ($rel_id != '') {
            $CI->load->model('projects_model');
            $data = $CI->projects_model->get($rel_id);
        } else {
            $where_projects = '';
            if ($CI->input->post('customer_id')) {
                $where_projects .= 'clientid='.$CI->input->post('customer_id');
            }
            $search = $CI->misc_model->_search_projects($q, 0, $where_projects);
            $data   = $search['result'];
        }
    } elseif ($type == 'staff') {
        if ($rel_id != '') {
            $CI->load->model('staff_model');
            $data = $CI->staff_model->get($rel_id);
        } else {
            $search = $CI->misc_model->_search_staff($q);
            $data   = $search['result'];
        }
    }

    return $data;
}
/**
 * Ger relation values eq invoice number or project name etc based on passed relation parsed results
 * from function get_relation_data
 * $relation can be object or array
 * @param  mixed $relation
 * @param  string $type
 * @return mixed
 */
function get_relation_values($relation, $type)
{
    if ($relation == '') {
        return array(
            'name' => '',
            'id' => '',
            'link' => '',
            'addedfrom' => 0,
            'subtext' => '',
            );
    }

    $addedfrom = 0;
    $name      = '';
    $id        = '';
    $link      = '';
    $subtext      = '';

    if ($type == 'customer' || $type == 'customers') {
        if (is_array($relation)) {
            $id   = $relation['userid'];
            $name = $relation['company'];
        } else {
            $id   = $relation->userid;
            $name = $relation->company;
        }
        $link = admin_url('clients/client/' . $id);
    } elseif ($type == 'contact' || $type == 'contacts') {
        if (is_array($relation)) {
            $userid = isset($relation['userid']) ? $relation['userid'] : $relation['relid'];
            $id   = $relation['id'];
            $name = $relation['firstname'] . ' ' . $relation['lastname'];
        } else {
            $userid = $relation->userid;
            $id   = $relation->id;
            $name = $relation->firstname . ' ' .$relation->lastname;
        }
        $subtext = get_company_name($userid);
        $link = admin_url('clients/client/' . $userid .'?contactid='.$id);
    } elseif ($type == 'invoice') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
        }
        $name      = format_invoice_number($id);
        $link = admin_url('invoices/list_invoices/' . $id);
    } elseif ($type == 'credit_note') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
        }
        $name      = format_credit_note_number($id);
        $link = admin_url('credit_notes/list_credit_notes/' . $id);
    } elseif ($type == 'estimate') {
        if (is_array($relation)) {
            $id        = $relation['estimateid'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
        }
        $name      = format_estimate_number($id);
        $link = admin_url('estimates/list_estimates/' . $id);
    } elseif ($type == 'contract' || $type == 'contracts') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $name      = $relation['subject'];
            $addedfrom = $relation['addedfrom'];
        } else {
            $id        = $relation->id;
            $name      = $relation->subject;
            $addedfrom = $relation->addedfrom;
        }
        $link = admin_url('contracts/contract/' . $id);
    } elseif ($type == 'ticket') {
        if (is_array($relation)) {
            $id   = $relation['ticketid'];
            $name = '#' . $relation['ticketid'];
            $name .= ' - ' . $relation['subject'];
        } else {
            $id   = $relation->ticketid;
            $name = '#' . $relation->ticketid;
            $name .= ' - ' . $relation->subject;
        }
        $link = admin_url('tickets/ticket/' . $id);
    } elseif ($type == 'expense' || $type == 'expenses') {
        if (is_array($relation)) {
            $id        = $relation['expenseid'];
            $name      = $relation['category_name'];
            $addedfrom = $relation['addedfrom'];

            if (!empty($relation['expense_name'])) {
                $name .= ' (' . $relation['expense_name'] . ')';
            }
        } else {
            $id        = $relation->expenseid;
            $name      = $relation->category_name;
            $addedfrom = $relation->addedfrom;
            if (!empty($relation->expense_name)) {
                $name .= ' (' . $relation->expense_name . ')';
            }
        }
        $link = admin_url('expenses/list_expenses/' . $id);
    } elseif ($type == 'lead' || $type == 'leads') {
        if (is_array($relation)) {
            $id   = $relation['id'];
            $name = $relation['name'];
            if ($relation['email'] != '') {
                $name .= ' - ' . $relation['email'];
            }
        } else {
            $id   = $relation->id;
            $name = $relation->name;
            if ($relation->email != '') {
                $name .= ' - ' . $relation->email;
            }
        }
        $link = admin_url('leads/index/' . $id);
    } elseif ($type == 'proposal') {
        if (is_array($relation)) {
            $id        = $relation['id'];
            $addedfrom = $relation['addedfrom'];
            if (!empty($relation['subject'])) {
                $name .= ' - ' . $relation['subject'];
            }
        } else {
            $id        = $relation->id;
            $addedfrom = $relation->addedfrom;
            if (!empty($relation->subject)) {
                $name .= ' - ' . $relation->subject;
            }
        }
        $name      = format_proposal_number($id);
        $link = admin_url('proposals/proposal/' . $id);
    } elseif ($type == 'tasks') {
        if (is_array($relation)) {
            $id   = $relation['id'];
            $name = $relation['name'];
        } else {
            $id   = $relation->id;
            $name = $relation->name;
        }
        $link = admin_url('tasks/view/' . $id);
    } elseif ($type == 'staff') {
        if (is_array($relation)) {
            $id   = $relation['staffid'];
            $name = $relation['firstname'] . ' ' . $relation['lastname'];
        } else {
            $id   = $relation->staffid;
            $name = $relation->firstname . ' ' . $relation->lastname;
        }
        $link = admin_url('profile/' . $id);
    } elseif ($type == 'project') {
        if (is_array($relation)) {
            $id   = $relation['id'];
            $name = $relation['name'];
            $clientId = $relation['clientid'];
        } else {
            $id   = $relation->id;
            $name = $relation->name;
            $clientId = $relation->clientid;
        }

        $name = '#' . $id . ' - '.$name . ' - '.get_company_name($clientId);

        $link = admin_url('projects/view/' . $id);
    }

    return do_action('relation_values', array(
        'name' => $name,
        'id' => $id,
        'link' => $link,
        'addedfrom' => $addedfrom,
        'subtext' => $subtext,
        'type'=>$type,
        ));
}

/**
 * Function used to render <option> for relation
 * This function will do all the necessary checking and return the options
 * @param  mixed $data
 * @param  string $type   rel_type
 * @param  string $rel_id rel_id
 * @return string
 */
function init_relation_options($data, $type, $rel_id = '')
{
    $_data = array();

    $has_permission_projects_view  = has_permission('projects', '', 'view');
    $has_permission_customers_view = has_permission('customers', '', 'view');
    $has_permission_contracts_view = has_permission('contracts', '', 'view');
    $has_permission_invoices_view  = has_permission('invoices', '', 'view');
    $has_permission_estimates_view = has_permission('estimates', '', 'view');
    $has_permission_expenses_view  = has_permission('expenses', '', 'view');
    $has_permission_proposals_view = has_permission('proposals', '', 'view');
    $is_admin                      = is_admin();
    $CI =& get_instance();
    $CI->load->model('projects_model');

    foreach ($data as $relation) {
        $relation_values = get_relation_values($relation, $type);
        if ($type == 'project') {
            if (!$has_permission_projects_view) {
                if (!$CI->projects_model->is_member($relation_values['id']) && $rel_id != $relation_values['id']) {
                    continue;
                }
            }
        } elseif ($type == 'lead') {
            if (!$is_admin) {
                if ($relation['assigned'] != get_staff_user_id() && $relation['addedfrom'] != get_staff_user_id() && $relation['is_public'] != 1 && $rel_id != $relation_values['id']) {
                    continue;
                }
            }
        } elseif ($type == 'customer') {
            if (!$has_permission_customers_view && !have_assigned_customers() && $rel_id != $relation_values['id']) {
                continue;
            } elseif (have_assigned_customers() && $rel_id != $relation_values['id'] && !$has_permission_customers_view) {
                if (!is_customer_admin($relation_values['id'])) {
                    continue;
                }
            }
        } elseif ($type == 'contract') {
            if (!$has_permission_contracts_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'invoice') {
            if (!$has_permission_invoices_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'estimate') {
            if (!$has_permission_estimates_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'expense') {
            if (!$has_permission_expenses_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        } elseif ($type == 'proposal') {
            if (!$has_permission_proposals_view && $rel_id != $relation_values['id'] && $relation_values['addedfrom'] != get_staff_user_id()) {
                continue;
            }
        }

        $_data[] = $relation_values;
        //  echo '<option value="' . $relation_values['id'] . '"' . $selected . '>' . $relation_values['name'] . '</option>';
    }

    return $_data;
}
