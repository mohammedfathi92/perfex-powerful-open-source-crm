<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Proposals extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('proposals_model');
        $this->load->model('currencies_model');
    }

    public function index($proposal_id = '')
    {
        $this->list_proposals($proposal_id);
    }

    public function list_proposals($proposal_id = '')
    {
        close_setup_menu();

        if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && get_option('allow_staff_view_proposals_assigned') == 0) {
            access_denied('proposals');
        }

        $isPipeline = $this->session->userdata('proposals_pipeline') == 'true';

        if ($isPipeline && !$this->input->get('status')) {
            $data['title']           = _l('proposals_pipeline');
            $data['bodyclass']       = 'proposals-pipeline';
            $data['switch_pipeline'] = false;
            // Direct access
            if (is_numeric($proposal_id)) {
                $data['proposalid'] = $proposal_id;
            } else {
                $data['proposalid'] = $this->session->flashdata('proposalid');
            }

            $this->load->view('admin/proposals/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['proposal_id'] = $proposal_id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('proposals');
            $data['statuses']              = $this->proposals_model->get_statuses();
            $data['proposals_sale_agents'] = $this->proposals_model->get_sale_agents();
            $data['years']                 = $this->proposals_model->get_proposals_years();
            $this->load->view('admin/proposals/manage', $data);
        }
    }

    public function table()
    {
        if (!has_permission('proposals', '', 'view')
            && !has_permission('proposals', '', 'view_own')
            && get_option('allow_staff_view_proposals_assigned') == 0) {
            ajax_access_denied();
        }

        $this->perfex_base->get_table_data('proposals');
    }

    public function proposal_relations($rel_id, $rel_type)
    {
        $this->perfex_base->get_table_data('proposals_relations', array(
            'rel_id' => $rel_id,
            'rel_type' => $rel_type,
        ));
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->proposals_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function sync_data()
    {
        if (has_permission('proposals', '', 'create') || has_permission('proposals', '', 'edit')) {
            $has_permission_view = has_permission('proposals', '', 'view');

            $this->db->where('rel_id', $this->input->post('rel_id'));
            $this->db->where('rel_type', $this->input->post('rel_type'));

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update('tblproposals', array(
                'phone' => $this->input->post('phone'),
                'zip' => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state' => $this->input->post('state'),
                'address' => $address,
                'city' => $this->input->post('city'),
            ));

            if ($this->db->affected_rows() > 0) {
                echo json_encode(array(
                    'message' => _l('all_data_synced_successfully'),
                ));
            } else {
                echo json_encode(array(
                    'message' => _l('sync_proposals_up_to_date'),
                ));
            }
        }
    }

    public function proposal($id = '')
    {
        if ($this->input->post()) {
            $proposal_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('proposals', '', 'create')) {
                    access_denied('proposals');
                }
                $id = $this->proposals_model->add($proposal_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('proposal')));
                    if ($this->set_proposal_pipeline_autoload($id)) {
                        redirect(admin_url('proposals'));
                    } else {
                        redirect(admin_url('proposals/list_proposals/' . $id));
                    }
                }
            } else {
                if (!has_permission('proposals', '', 'edit')) {
                    access_denied('proposals');
                }
                $success = $this->proposals_model->update($proposal_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('proposal')));
                }
                if ($this->set_proposal_pipeline_autoload($id)) {
                    redirect(admin_url('proposals'));
                } else {
                    redirect(admin_url('proposals/list_proposals/' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('proposal_lowercase'));
        } else {
            $data['proposal'] = $this->proposals_model->get($id);

            if (!$data['proposal'] || !$this->user_can_view_proposal($id)) {
                blank_page(_l('proposal_not_found'));
            }

            $data['estimate']    = $data['proposal'];
            $data['is_proposal'] = true;
            $title               = _l('edit', _l('proposal_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items']        = $this->invoice_items_model->get_grouped();
        } else {
            $data['items'] = array();
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['statuses']   = $this->proposals_model->get_statuses();
        $data['staff']      = $this->staff_model->get('', 1);
        $data['currencies'] = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title']      = $title;
        $this->load->view('admin/proposals/proposal', $data);
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/proposals/templates/' . $name, array(), true);
    }

    public function send_expiry_reminder($id)
    {
        $canView = $this->user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        $success = $this->proposals_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('proposals/list_proposals/' . $id));
        }
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update('tblproposals', get_acceptance_info_array(true));
        }

        redirect(admin_url('proposals/list_proposals/'.$id));
    }

    public function pdf($id)
    {
        $canView = $this->user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        if (!$id) {
            redirect(admin_url('proposals'));
        }
        $proposal = $this->proposals_model->get($id);

        try {
            $pdf      = proposal_pdf($proposal);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type     = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $proposal_number = format_proposal_number($id);
        $pdf->Output($proposal_number . '.pdf', $type);
    }

    public function get_proposal_data_ajax($id, $to_return = false)
    {
        if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && get_option('allow_staff_view_proposals_assigned') == 0) {
            echo _l('access_denied');
            die;
        }

        $proposal = $this->proposals_model->get($id, array(), true);

        if (!$proposal) {
            echo _l('proposal_not_found');
            die;
        } else {
            if (!$this->user_can_view_proposal($id)) {
                echo _l('proposal_not_found');
                die;
            }
        }

        $template_name         = 'proposal-send-to-customer';
        $data['template_name'] = $template_name;

        $this->db->where('slug', $template_name);
        $this->db->where('language', 'english');
        $template_result = $this->db->get('tblemailtemplates')->row();

        $data['template_system_name'] = $template_result->name;
        $data['template_id'] = $template_result->emailtemplateid;

        $data['template_disabled'] = false;
        if (total_rows('tblemailtemplates', array('slug'=>$data['template_name'], 'active'=>0)) > 0) {
            $data['template_disabled'] = true;
        }

        define('EMAIL_TEMPLATE_PROPOSAL_ID_HELP', $proposal->id);

        $data['template']      = get_email_template_for_sending($template_name, $proposal->email);

        $proposal_merge_fields  = get_available_merge_fields();
        $_proposal_merge_fields = array();
        array_push($_proposal_merge_fields, array(
            array(
                'name' => 'Items Table',
                'key' => '{proposal_items}',
            ),
        ));
        foreach ($proposal_merge_fields as $key => $val) {
            foreach ($val as $type => $f) {
                if ($type == 'proposals') {
                    foreach ($f as $available) {
                        foreach ($available['available'] as $av) {
                            if ($av == 'proposals') {
                                array_push($_proposal_merge_fields, $f);
                                break;
                            }
                        }
                        break;
                    }
                } elseif ($type == 'other') {
                    array_push($_proposal_merge_fields, $f);
                }
            }
        }
        $data['proposal_statuses']     = $this->proposals_model->get_statuses();
        $data['members']               = $this->staff_model->get('', 1);
        $data['proposal_merge_fields'] = $_proposal_merge_fields;
        $data['proposal']              = $proposal;
        if ($to_return == false) {
            $this->load->view('admin/proposals/proposals_preview_template', $data);
        } else {
            return $this->load->view('admin/proposals/proposals_preview_template', $data, true);
        }
    }

    public function convert_to_estimate($id)
    {
        if (!has_permission('estimates', '', 'create')) {
            access_denied('estimates');
        }
        if ($this->input->post()) {
            $this->load->model('estimates_model');
            $estimate_id = $this->estimates_model->add($this->input->post());
            if ($estimate_id) {
                set_alert('success', _l('proposal_converted_to_estimate_success'));
                $this->db->where('id', $id);
                $this->db->update('tblproposals', array(
                    'estimate_id' => $estimate_id,
                    'status' => 3,
                ));
                logActivity('Proposal Converted to Estimate [EstimateID: ' . $estimate_id . ', ProposalID: ' . $id . ']');

                do_action('proposal_converted_to_estimate', array('proposal_id'=>$id, 'estimate_id'=>$estimate_id));

                redirect(admin_url('estimates/estimate/' . $estimate_id));
            } else {
                set_alert('danger', _l('proposal_converted_to_estimate_fail'));
            }
            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect(admin_url('proposals'));
            } else {
                redirect(admin_url('proposals/list_proposals/' . $id));
            }
        }
    }

    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if ($this->input->post()) {
            $this->load->model('invoices_model');
            $invoice_id = $this->invoices_model->add($this->input->post());
            if ($invoice_id) {
                set_alert('success', _l('proposal_converted_to_invoice_success'));
                $this->db->where('id', $id);
                $this->db->update('tblproposals', array(
                    'invoice_id' => $invoice_id,
                    'status' => 3,
                ));
                logActivity('Proposal Converted to Invoice [InvoiceID: ' . $invoice_id . ', ProposalID: ' . $id . ']');
                do_action('proposal_converted_to_invoice', array('proposal_id'=>$id, 'invoice_id'=>$invoice_id));
                redirect(admin_url('invoices/invoice/' . $invoice_id));
            } else {
                set_alert('danger', _l('proposal_converted_to_invoice_fail'));
            }
            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect(admin_url('proposals'));
            } else {
                redirect(admin_url('proposals/list_proposals/' . $id));
            }
        }
    }

    public function get_invoice_convert_data($id)
    {
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

        $data['staff']          = $this->staff_model->get('', 1);
        $data['proposal']       = $this->proposals_model->get($id);
        $data['billable_tasks'] = array();
        $data['add_items']      = $this->_parse_items($data['proposal']);

        if ($data['proposal']->rel_type == 'lead') {
            $this->db->where('leadid', $data['proposal']->rel_id);
            $data['customer_id'] = $this->db->get('tblclients')->row()->userid;
        } else {
            $data['customer_id'] = $data['proposal']->rel_id;
        }
        $this->load->view('admin/proposals/invoice_convert_template', $data);
    }

    public function get_estimate_convert_data($id)
    {
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

        $data['staff']     = $this->staff_model->get('', 1);
        $data['proposal']  = $this->proposals_model->get($id);
        $data['add_items'] = $this->_parse_items($data['proposal']);

        $this->load->model('estimates_model');
        $data['estimate_statuses']  = $this->estimates_model->get_statuses();
        if ($data['proposal']->rel_type == 'lead') {
            $this->db->where('leadid', $data['proposal']->rel_id);
            $data['customer_id'] = $this->db->get('tblclients')->row()->userid;
        } else {
            $data['customer_id'] = $data['proposal']->rel_id;
        }
        $this->load->view('admin/proposals/estimate_convert_template', $data);
    }

    private function _parse_items($proposal)
    {
        $items = array();
        foreach ($proposal->items as $item) {
            $taxnames = array();
            $taxes    = get_proposal_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                array_push($taxnames, $tax['taxname']);
            }
            $item['taxname'] = $taxnames;
            $item['id']      = 0;
            $items[]         = $item;
        }

        return $items;
    }

    /* Send proposal to email */
    public function send_to_email($id)
    {
        $canView = $this->user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        if ($this->input->post()) {
            $success = $this->proposals_model->send_proposal_to_email($id, 'proposal-send-to-customer', $this->input->post('attach_pdf'), $this->input->post('cc'));
            if ($success) {
                set_alert('success', _l('proposal_sent_to_email_success'));
            } else {
                set_alert('danger', _l('proposal_sent_to_email_fail'));
            }

            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('proposals/list_proposals/' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('proposals', '', 'create')) {
            access_denied('proposals');
        }
        $new_id = $this->proposals_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('proposal_copy_success'));
            if ($this->set_proposal_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('proposals/list_proposals/' . $new_id));
            }
        } else {
            set_alert('success', _l('proposal_copy_fail'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect(admin_url('proposals'));
        } else {
            redirect(admin_url('proposals/list_proposals/' . $id));
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('proposals', '', 'edit')) {
            access_denied('proposals');
        }
        $success = $this->proposals_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('proposal_status_changed_success'));
        } else {
            set_alert('danger', _l('proposal_status_changed_fail'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect(admin_url('proposals'));
        } else {
            redirect(admin_url('proposals/list_proposals/' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('proposals', '', 'delete')) {
            access_denied('proposals');
        }
        $response = $this->proposals_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('proposal')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('proposal_lowercase')));
        }
        redirect(admin_url('proposals'));
    }

    public function get_relation_data_values($rel_id, $rel_type)
    {
        echo json_encode($this->proposals_model->get_relation_data_values($rel_id, $rel_type));
    }

    public function add_proposal_comment()
    {
        if ($this->input->post()) {
            echo json_encode(array(
                'success' => $this->proposals_model->add_comment($this->input->post()),
            ));
        }
    }

    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode(array(
                'success' => $this->proposals_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ));
        }
    }

    public function get_proposal_comments($id)
    {
        $data['comments'] = $this->proposals_model->get_comments($id);
        $this->load->view('admin/proposals/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get('tblproposalcomments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode(array(
                    'success' => false,
                ));
                die;
            }
            echo json_encode(array(
                'success' => $this->proposals_model->remove_comment($id),
            ));
        } else {
            echo json_encode(array(
                'success' => false,
            ));
        }
    }

    public function save_proposal_data()
    {
        if (!has_permission('proposals', '', 'edit') && !has_permission('proposals', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode(array(
                'success' => false,
                'message' => _l('access_denied'),
            ));
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('proposal_id'));
        $this->db->update('tblproposals', array(
            'content' => $this->input->post('content', false),
        ));

        if ($this->db->affected_rows() > 0) {
            $success = true;
            $message = _l('updated_successfully', _l('proposal'));
        }

        echo json_encode(array(
            'success' => $success,
            'message' => $message,
        ));
    }

    // Pipeline
    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata(array(
            'proposals_pipeline' => $set,
        ));
        if ($manual == false) {
            redirect(admin_url('proposals'));
        }
    }

    public function pipeline_open($id)
    {
        if (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || get_option('allow_staff_view_proposals_assigned') == 1) {
            $data['proposal']      = $this->get_proposal_data_ajax($id, true);
            $data['proposal_data'] = $this->proposals_model->get($id);
            $this->load->view('admin/proposals/pipeline/proposal', $data);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('proposals', '', 'edit')) {
            $this->proposals_model->update_pipeline($this->input->post());
        }
    }

    public function get_pipeline()
    {
        if (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || get_option('allow_staff_view_proposals_assigned') == 1) {
            $data['statuses'] = $this->proposals_model->get_statuses();
            $this->load->view('admin/proposals/pipeline/pipeline', $data);
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $proposals = $this->proposals_model->do_kanban_query($status, $this->input->get('search'), $page, array(
            'sort_by' => $this->input->get('sort_by'),
            'sort' => $this->input->get('sort'),
        ));

        foreach ($proposals as $proposal) {
            $this->load->view('admin/proposals/pipeline/_kanban_card', array(
                'proposal' => $proposal,
                'status' => $status,
            ));
        }
    }

    public function set_proposal_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('proposals_pipeline') && $this->session->userdata('proposals_pipeline') == 'true') {
            $this->session->set_flashdata('proposalid', $id);

            return true;
        }

        return false;
    }

    private function user_can_view_proposal($id)
    {
        $this->db->select('id,addedfrom,assigned');
        $this->db->from('tblproposals');
        $this->db->where('id', $id);
        $proposal = $this->db->get()->row();

        if (!has_permission('proposals', '', 'view')) {
            if ($proposal->addedfrom != get_staff_user_id() && $proposal->assigned != get_staff_user_id()) {
                return false;
            } else {
                if (has_permission('proposals', '', 'view_own') && $proposal->addedfrom != get_staff_user_id() && $proposal->assigned != get_staff_user_id()) {
                    return false;
                } elseif (!has_permission('proposals', '', 'view_own') && $proposal->addedfrom == get_staff_user_id()) {
                    return false;
                } else {
                    if ($proposal->assigned == get_staff_user_id()) {
                        if (get_option('allow_staff_view_proposals_assigned') == 0) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}
