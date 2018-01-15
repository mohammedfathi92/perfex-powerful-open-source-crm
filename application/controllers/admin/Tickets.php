<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Tickets extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        if (get_option('access_tickets_to_none_staff_members') == 0 && !is_staff_member()) {
            redirect(admin_url());
        }
        $this->load->model('tickets_model');
    }

    public function index($status = '', $userid = '')
    {
        close_setup_menu();

        if (!is_numeric($status)) {
            $status = '';
        }

        if ($this->input->is_ajax_request()) {
            if (!$this->input->post('filters_ticket_id')) {
                $tableParams = array(
                    'status' => $status,
                    'userid' => $userid,
                );
            } else {
                // request for othes tickets when single ticket is opened
                $tableParams = array(
                'userid' => $this->input->post('filters_userid'),
                'where_not_ticket_id' => $this->input->post('filters_ticket_id'),
            );
                if ($tableParams['userid'] == 0) {
                    unset($tableParams['userid']);
                    $tableParams['by_email'] = $this->input->post('filters_email');
                }
            }

            $this->perfex_base->get_table_data('tickets', $tableParams);
        }

        $data['chosen_ticket_status']              = $status;
        $data['weekly_tickets_opening_statistics'] = json_encode($this->tickets_model->get_weekly_tickets_opening_statistics());
        $data['title']                             = _l('support_tickets');
        $this->load->model('departments_model');
        $data['statuses']             = $this->tickets_model->get_ticket_status();
        $data['staff_deparments_ids'] = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
        $data['departments']          = $this->departments_model->get();
        $data['priorities']           = $this->tickets_model->get_priority();
        $data['services']             = $this->tickets_model->get_service();
        $data['ticket_assignees']     = $this->tickets_model->get_tickets_assignes_disctinct();
        $data['bodyclass']            = 'tickets_page';

        $data['default_tickets_list_statuses'] = do_action('default_tickets_list_statuses', array(1, 2, 4));
        $this->load->view('admin/tickets/list', $data);
    }

    public function add($userid = false)
    {
        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['message'] = $this->input->post('message', false);
            $id              = $this->tickets_model->add($data, get_staff_user_id());
            if ($id) {
                set_alert('success', _l('new_ticket_added_successfully', $id));
                redirect(admin_url('tickets/ticket/' . $id));
            }
        }
        if ($userid !== false) {
            $data['userid'] = $userid;
            $data['client'] = $this->clients_model->get($userid);
        }
        // Load necessary models
        $this->load->model('knowledge_base_model');
        $this->load->model('departments_model');

        $data['departments']        = $this->departments_model->get();
        $data['predefined_replies'] = $this->tickets_model->get_predefined_reply();
        $data['priorities']         = $this->tickets_model->get_priority();
        $data['services']           = $this->tickets_model->get_service();
        $data['staff']              = $this->staff_model->get();
        $data['articles']           = $this->knowledge_base_model->get();
        $data['bodyclass']          = 'ticket';
        $data['title']              = _l('new_ticket');

        if ($this->input->get('project_id') && $this->input->get('project_id') > 0) {
            // request from project area to create new ticket
            $data['project_id'] = $this->input->get('project_id');
            $data['userid'] = get_client_id_by_project_id($data['project_id']);
            if (total_rows('tblcontacts', array('active'=>1, 'userid'=>$data['userid'])) == 1) {
                $contact = $this->clients_model->get_contacts($data['userid']);
                if (isset($contact[0])) {
                    $data['contact'] = $contact[0];
                }
            }
        } elseif ($this->input->get('contact_id') && $this->input->get('contact_id') > 0 && $this->input->get('userid')) {
            $userid = $this->input->get('userid');
            // request from customer profile to create new ticket only if there is 1 contact
            $contact = $this->clients_model->get_contacts($userid);
            if (total_rows('tblcontacts', array('active'=>1, 'userid'=>$userid)) == 1) {
                $contact = $this->clients_model->get_contacts($userid);
                if (isset($contact[0])) {
                    $data['contact'] = $contact[0];
                }
            }
        }
        $this->load->view('admin/tickets/add', $data);
    }

    public function delete($ticketid)
    {
        if (!$ticketid) {
            redirect(admin_url('tickets'));
        }

        $response = $this->tickets_model->delete($ticketid);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('ticket')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_lowercase')));
        }


        if (strpos($_SERVER['HTTP_REFERER'], 'tickets/ticket') !== false) {
            redirect(admin_url('tickets'));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function ticket($id)
    {
        if (!$id) {
            redirect(admin_url('tickets/add'));
        }

        $data['ticket'] = $this->tickets_model->get_ticket_by_id($id);

        if (!$data['ticket']) {
            blank_page(_l('ticket_not_found'));
        }

        if (get_option('staff_access_only_assigned_departments') == 1) {
            if (!is_admin()) {
                $this->load->model('departments_model');
                $staff_departments = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                if (!in_array($data['ticket']->department, $staff_departments)) {
                    set_alert('danger', _l('ticket_access_by_department_denied'));
                    redirect(admin_url('access_denied'));
                }
            }
        }

        if ($this->input->post()) {
            $returnToTicketList = false;
            $data            = $this->input->post();

            if (isset($data['ticket_add_response_and_back_to_list'])) {
                $returnToTicketList = true;
                unset($data['ticket_add_response_and_back_to_list']);
            }

            $data['message'] = $this->input->post('message', false);
            $replyid         = $this->tickets_model->add_reply($data, $id, get_staff_user_id());

            if ($replyid) {
                set_alert('success', _l('replied_to_ticket_successfully', $id));
            }
            if (!$returnToTicketList) {
                redirect(admin_url('tickets/ticket/' . $id));
            } else {
                redirect(admin_url('tickets'));
            }
        }
        // Load necessary models
        $this->load->model('knowledge_base_model');
        $this->load->model('departments_model');

        $data['statuses']                       = $this->tickets_model->get_ticket_status();
        $data['statuses']['callback_translate'] = 'ticket_status_translate';

        $data['departments']          = $this->departments_model->get();
        $data['predefined_replies']   = $this->tickets_model->get_predefined_reply();
        $data['priorities']           = $this->tickets_model->get_priority();
        $data['services']             = $this->tickets_model->get_service();
        $data['staff']                = $this->staff_model->get();
        $data['articles']             = $this->knowledge_base_model->get();
        $data['ticket_replies']       = $this->tickets_model->get_ticket_replies($id);
        $data['bodyclass']            = 'top-tabs ticket single-ticket';
        $data['title']                = $data['ticket']->subject;
        $data['ticket']->ticket_notes = $this->misc_model->get_notes($id, 'ticket');
        $this->load->view('admin/tickets/single', $data);
    }

    public function edit_message()
    {
        if ($this->input->post()) {
            $data         = $this->input->post();
            $data['data'] = $this->input->post('data', false);

            if ($data['type'] == 'reply') {
                $this->db->where('id', $data['id']);
                $this->db->update('tblticketreplies', array(
                    'message' => $data['data'],
                ));
            } elseif ($data['type'] == 'ticket') {
                $this->db->where('ticketid', $data['id']);
                $this->db->update('tbltickets', array(
                    'message' => $data['data'],
                ));
            }
            if ($this->db->affected_rows() > 0) {
                set_alert('success', _l('ticket_message_updated_successfully'));
            }
            redirect(admin_url('tickets/ticket/' . $data['main_ticket']));
        }
    }

    public function delete_ticket_reply($ticket_id, $reply_id)
    {
        if (!$reply_id) {
            redirect(admin_url('tickets'));
        }
        $response = $this->tickets_model->delete_ticket_reply($ticket_id, $reply_id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('ticket_reply')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_reply')));
        }
        redirect(admin_url('tickets/ticket/' . $ticket_id));
    }

    public function change_status_ajax($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->tickets_model->change_ticket_status($id, $status));
        }
    }

    public function update_single_ticket_settings()
    {
        if ($this->input->post()) {
            $this->session->mark_as_flash('active_tab');
            $this->session->mark_as_flash('active_tab_settings');
            $success = $this->tickets_model->update_single_ticket_settings($this->input->post());
            if ($success) {
                $this->session->set_flashdata('active_tab', true);
                $this->session->set_flashdata('active_tab_settings', true);
                if (get_option('staff_access_only_assigned_departments') == 1) {
                    $ticket = $this->tickets_model->get_ticket_by_id($this->input->post('ticketid'));
                    $this->load->model('departments_model');
                    $staff_departments = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
                    if (!in_array($ticket->department, $staff_departments) && !is_admin()) {
                        set_alert('success', _l('ticket_settings_updated_successfully_and_reassigned', $ticket->department_name));
                        echo json_encode(array(
                            'success' => $success,
                            'department_reassigned' => true,
                        ));
                        die();
                    }
                }
                set_alert('success', _l('ticket_settings_updated_successfully'));
            }
            echo json_encode(array(
                'success' => $success,
            ));
            die();
        }
    }

    // Priorities
    /* Get all ticket priorities */
    public function priorities()
    {
        if (!is_admin()) {
            access_denied('Ticket Priorities');
        }
        $data['priorities'] = $this->tickets_model->get_priority();
        $data['title']      = _l('ticket_priorities');
        $this->load->view('admin/tickets/priorities/manage', $data);
    }

    /* Add new priority od update existing*/
    public function priority()
    {
        if (!is_admin()) {
            access_denied('Ticket Priorities');
        }
        if ($this->input->post()) {
            if (!$this->input->post('id')) {
                $id = $this->tickets_model->add_priority($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('ticket_priority')));
                }
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->tickets_model->update_priority($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('ticket_priority')));
                }
            }
            die;
        }
    }

    /* Delete ticket priority */
    public function delete_priority($id)
    {
        if (!is_admin()) {
            access_denied('Ticket Priorities');
        }
        if (!$id) {
            redirect(admin_url('tickets/priorities'));
        }
        $response = $this->tickets_model->delete_priority($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('ticket_priority_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('ticket_priority')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_priority_lowercase')));
        }
        redirect(admin_url('tickets/priorities'));
    }

    /* List all ticket predefined replies */
    public function predefined_replies()
    {
        if (!is_admin()) {
            access_denied('Predefined Replies');
        }
        if ($this->input->is_ajax_request()) {
            $aColumns     = array(
                'name',
            );
            $sIndexColumn = "id";
            $sTable       = 'tblpredefinedreplies';
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(), array(
                'id',
            ));
            $output       = $result['output'];
            $rResult      = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = array();
                for ($i = 0; $i < count($aColumns); $i++) {
                    $_data = $aRow[$aColumns[$i]];
                    if ($aColumns[$i] == 'name') {
                        $_data = '<a href="' . admin_url('tickets/predefined_reply/' . $aRow['id']) . '">' . $_data . '</a>';
                    }
                    $row[] = $_data;
                }
                $options            = icon_btn('tickets/predefined_reply/' . $aRow['id'], 'pencil-square-o');
                $row[]              = $options .= icon_btn('tickets/delete_predefined_reply/' . $aRow['id'], 'remove', 'btn-danger _delete');
                $output['aaData'][] = $row;
            }
            echo json_encode($output);
            die();
        }
        $data['title'] = _l('predefined_replies');
        $this->load->view('admin/tickets/predefined_replies/manage', $data);
    }

    public function get_predefined_reply_ajax($id)
    {
        echo json_encode($this->tickets_model->get_predefined_reply($id));
    }

    public function ticket_change_data()
    {
        if ($this->input->is_ajax_request()) {
            $contact_id = $this->input->post('contact_id');
            echo json_encode(array(
                'contact_data' => $this->clients_model->get_contact($contact_id),
                'customer_has_projects' => customer_has_projects(get_user_id_by_contact_id($contact_id)),
            ));
        }
    }

    /* Add new reply or edit existing */
    public function predefined_reply($id = '')
    {
        if (!is_admin() && get_option('staff_members_save_tickets_predefined_replies') == '0') {
            access_denied('Predefined Reply');
        }
        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['message'] = $this->input->post('message', false);
            $ticketAreaRequest = isset($data['ticket_area']);

            if(isset($data['ticket_area'])) {
                unset($data['ticket_area']);
            }

            if ($id == '') {
                $id = $this->tickets_model->add_predefined_reply($data);
                if(!$ticketAreaRequest){
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('predefined_reply')));
                        redirect(admin_url('tickets/predefined_reply/' . $id));
                    }
                } else {
                    echo json_encode(array('success'=>$id ? true : false, 'id'=>$id));
                    die;
                }
            } else {
                $success = $this->tickets_model->update_predefined_reply($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('predefined_reply')));
                }
                redirect(admin_url('tickets/predefined_reply/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('predefined_reply_lowercase'));
        } else {
            $predefined_reply         = $this->tickets_model->get_predefined_reply($id);
            $data['predefined_reply'] = $predefined_reply;
            $title                    = _l('edit', _l('predefined_reply_lowercase')) . ' ' . $predefined_reply->name;
        }
        $data['title'] = $title;
        $this->load->view('admin/tickets/predefined_replies/reply', $data);
    }

    /* Delete ticket reply from database */
    public function delete_predefined_reply($id)
    {
        if (!is_admin()) {
            access_denied('Delete Predefined Reply');
        }
        if (!$id) {
            redirect(admin_url('tickets/predefined_replies'));
        }
        $response = $this->tickets_model->delete_predefined_reply($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('predefined_reply')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('predefined_reply_lowercase')));
        }
        redirect(admin_url('tickets/predefined_replies'));
    }

    // Ticket statuses
    /* Get all ticket statuses */
    public function statuses()
    {
        if (!is_admin()) {
            access_denied('Ticket Statuses');
        }
        $data['statuses'] = $this->tickets_model->get_ticket_status();
        $data['title']    = 'Ticket statuses';
        $this->load->view('admin/tickets/tickets_statuses/manage', $data);
    }

    /* Add new or edit existing status */
    public function status()
    {
        if (!is_admin()) {
            access_denied('Ticket Statuses');
        }
        if ($this->input->post()) {
            if (!$this->input->post('id')) {
                $id = $this->tickets_model->add_ticket_status($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('ticket_status')));
                }
            } else {
                $data = $this->input->post();
                $id   = $data['id'];
                unset($data['id']);
                $success = $this->tickets_model->update_ticket_status($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('ticket_status')));
                }
            }
            die;
        }
    }

    /* Delete ticket status from database */
    public function delete_ticket_status($id)
    {
        if (!is_admin()) {
            access_denied('Ticket Statuses');
        }
        if (!$id) {
            redirect(admin_url('tickets/statuses'));
        }
        $response = $this->tickets_model->delete_ticket_status($id);
        if (is_array($response) && isset($response['default'])) {
            set_alert('warning', _l('cant_delete_default', _l('ticket_status_lowercase')));
        } elseif (is_array($response) && isset($response['referenced'])) {
            set_alert('danger', _l('is_referenced', _l('ticket_status_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('ticket_status')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('ticket_status_lowercase')));
        }
        redirect(admin_url('tickets/statuses'));
    }

    /* List all ticket services */
    public function services()
    {
        if (!is_admin()) {
            access_denied('Ticket Services');
        }
        if ($this->input->is_ajax_request()) {
            $aColumns     = array(
                'name',
            );
            $sIndexColumn = "serviceid";
            $sTable       = 'tblservices';
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(), array(
                'serviceid',
            ));
            $output       = $result['output'];
            $rResult      = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = array();
                for ($i = 0; $i < count($aColumns); $i++) {
                    $_data = $aRow[$aColumns[$i]];
                    if ($aColumns[$i] == 'name') {
                        $_data = '<a href="#" onclick="edit_service(this,' . $aRow['serviceid'] . ');return false" data-name="' . $aRow['name'] . '">' . $_data . '</a>';
                    }
                    $row[] = $_data;
                }
                $options            = icon_btn('#', 'pencil-square-o', 'btn-default', array(
                    'data-name' => $aRow['name'],
                    'onclick' => 'edit_service(this,' . $aRow['serviceid'] . '); return false;',
                ));
                $row[]              = $options .= icon_btn('tickets/delete_service/' . $aRow['serviceid'], 'remove', 'btn-danger _delete');
                $output['aaData'][] = $row;
            }
            echo json_encode($output);
            die();
        }
        $data['title'] = _l('services');
        $this->load->view('admin/tickets/services/manage', $data);
    }

    /* Add new service od delete existing one */
    public function service($id = '')
    {
        if (!is_admin() && get_option('staff_members_save_tickets_predefined_replies') == '0') {
            access_denied('Ticket Services');
        }

        if ($this->input->post()) {
            $post_data = $this->input->post();
            if (!$this->input->post('id')) {
                $requestFromTicketArea = isset($post_data['ticket_area']);
                if (isset($post_data['ticket_area'])) {
                    unset($post_data['ticket_area']);
                }
                $id = $this->tickets_model->add_service($post_data);
                if (!$requestFromTicketArea) {
                    if ($id) {
                        set_alert('success', _l('added_successfully', _l('service')));
                    }
                } else {
                    echo json_encode(array('success'=>$id ? true : false, 'id'=>$id, 'name'=>$post_data['name']));
                }
            } else {
                $id   = $post_data['id'];
                unset($post_data['id']);
                $success = $this->tickets_model->update_service($post_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('service')));
                }
            }
            die;
        }
    }

    /* Delete ticket service from database */
    public function delete_service($id)
    {
        if (!is_admin()) {
            access_denied('Ticket Services');
        }
        if (!$id) {
            redirect(admin_url('tickets/services'));
        }
        $response = $this->tickets_model->delete_service($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('service_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('service')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('service_lowercase')));
        }
        redirect(admin_url('tickets/services'));
    }

    public function spam_filters($type = '')
    {
        if (!is_admin()) {
            access_denied('Tickets Spam Filters');
        }
        if ($this->input->is_ajax_request()) {
            $aColumns     = array(
                'value',
            );
            $sIndexColumn = "id";
            $sTable       = 'tblticketsspamcontrol';
            $result       = data_tables_init($aColumns, $sIndexColumn, $sTable, array(), array(
                'AND type ="' . $type . '"',
            ), array(
                'id',
            ));
            $output       = $result['output'];
            $rResult      = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = array();
                for ($i = 0; $i < count($aColumns); $i++) {
                    $_data = $aRow[$aColumns[$i]];
                    $row[] = $_data;
                }
                $options            = icon_btn('#', 'pencil-square-o', 'btn-default', array(
                    'onclick' => 'edit_spam_filter(this,' . $aRow['id'] . '); return false;',
                    'data-value' => $aRow['value'],
                    'data-type' => $type,
                ));
                $row[]              = $options .= icon_btn('tickets/delete_spam_filter/' . $aRow['id'], 'remove', 'btn-danger _delete');
                $output['aaData'][] = $row;
            }
            echo json_encode($output);
            die();
        }
        $data['title'] = _l('spam_filters');
        $this->load->view('admin/tickets/spam_filters', $data);
    }

    public function spam_filter()
    {
        if (!is_admin()) {
            access_denied('Manage Tickets Spam Filters');
        }
        if ($this->input->post()) {
            if ($this->input->post('id')) {
                $success = $this->tickets_model->edit_spam_filter($this->input->post());
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('spam_filter'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message,
                ));
            } else {
                $success = $this->tickets_model->add_spam_filter($this->input->post());
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('spam_filter'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message,
                ));
            }
        }
    }

    public function delete_spam_filter($id)
    {
        if (!is_admin()) {
            access_denied('Delete Ticket Spam Filter');
        }
        $success = $this->tickets_model->delete_spam_filter($id);
        if ($success) {
            set_alert('success', _l('deleted', _l('spam_filter')));
        }
        redirect(admin_url('tickets/spam_filters'));
    }

    public function block_sender()
    {
        if ($this->input->post()) {
            $this->db->insert('tblticketsspamcontrol', array(
                'type' => 'sender',
                'value' => $this->input->post('sender'),
            ));
            $insert_id = $this->db->insert_id();
            if ($insert_id) {
                set_alert('success', _l('sender_blocked_successfully'));
            }
        }
    }

    public function bulk_action()
    {
        do_action('before_do_bulk_action_for_tickets');
        if ($this->input->post()) {
            $total_deleted = 0;
            $ids           = $this->input->post('ids');
            $status        = $this->input->post('status');
            $department    = $this->input->post('department');
            $service       = $this->input->post('service');
            $priority      = $this->input->post('priority');
            $tags      = $this->input->post('tags');
            $is_admin      = is_admin();
            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($is_admin) {
                            if ($this->tickets_model->delete($id)) {
                                $total_deleted++;
                            }
                        }
                    } else {
                        if ($status) {
                            $this->db->where('ticketid', $id);
                            $this->db->update('tbltickets', array(
                                'status' => $status,
                            ));
                        }
                        if ($department) {
                            $this->db->where('ticketid', $id);
                            $this->db->update('tbltickets', array(
                                'department' => $department,
                            ));
                        }
                        if ($priority) {
                            $this->db->where('ticketid', $id);
                            $this->db->update('tbltickets', array(
                                'priority' => $priority,
                            ));
                        }

                        if ($service) {
                            $this->db->where('ticketid', $id);
                            $this->db->update('tbltickets', array(
                                'service' => $service,
                            ));
                        }
                        if ($tags) {
                            handle_tags_save($tags, $id, 'ticket');
                        }
                    }
                }
            }

            if ($this->input->post('mass_delete')) {
                set_alert('success', _l('total_tickets_deleted', $total_deleted));
            }
        }
    }
}
