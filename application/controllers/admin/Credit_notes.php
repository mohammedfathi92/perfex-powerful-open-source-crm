<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Credit_notes extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('credit_notes_model');
    }

    /* Get all credit ntoes in case user go on index page */
    public function index($id = '')
    {
        $this->list_credit_notes($id);
    }

    /* List all credit ntoes datatables */
    public function list_credit_notes($id = '')
    {
        if (!has_permission('credit_notes', '', 'view') && !has_permission('credit_notes', '', 'view_own')) {
            access_denied('credit_notes');
        }

        close_setup_menu();

        $data['years'] = $this->credit_notes_model->get_credits_years();
        $data['statuses'] = $this->credit_notes_model->get_statuses();
        $data['credit_note_id'] = $id;
        $data['title']                = _l('credit_notes');
        $this->load->view('admin/credit_notes/manage', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('credit_notes', '', 'view') && !has_permission('credit_notes', '', 'view_own')) {
            ajax_access_denied();
        }

        $this->perfex_base->get_table_data('credit_notes', array(
            'clientid' => $clientid,
        ));
    }

    public function update_number_settings($id)
    {
        $response = array(
            'success' => false,
            'message' => '',
        );
        if (has_permission('credit_notes', '', 'edit')) {
            if ($this->input->post('prefix')) {
                $affected_rows = 0;

                $this->db->where('id', $id);
                $this->db->update('tblcreditnotes', array(
                    'prefix' => $this->input->post('prefix'),
                ));
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }

                if ($affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = _l('updated_successfully', _l('credit_note'));
                }
            }
        }
        echo json_encode($response);
        die;
    }

    public function validate_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');
        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }
        if (total_rows('tblcreditnotes', array(
            'number' => $number,
        )) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    /* Add new invoice or update existing */
    public function credit_note($id = '')
    {
        if (!has_permission('credit_notes', '', 'view') && !has_permission('credit_notes', '', 'view_own')) {
            access_denied('credit_notes');
        }
        if ($this->input->post()) {
            $credit_note_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('credit_notes', '', 'create')) {
                    access_denied('credit_notes');
                }
                $id = $this->credit_notes_model->add($credit_note_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('credit_note')));
                    redirect(admin_url('credit_notes/list_credit_notes/' . $id));
                }
            } else {
                if (!has_permission('credit_notes', '', 'edit')) {
                    access_denied('credit_notes');
                }
                $success = $this->credit_notes_model->update($credit_note_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('credit_note')));
                }
                redirect(admin_url('credit_notes/list_credit_notes/' . $id));
            }
        }
        if ($id == '') {
            $title                  = _l('add_new', _l('credit_note_lowercase'));
        } else {
            $credit_note = $this->credit_notes_model->get($id);

            if (!$credit_note || (!has_permission('credit_notes', '', 'view') && $credit_note->addedfrom != get_staff_user_id())) {
                blank_page(_l('credit_note_not_found'), 'danger');
            }

            $data['credit_note']        = $credit_note;
            $data['edit']           = true;
            $title                  = _l('edit', _l('credit_note_lowercase')) . ' - ' . format_credit_note_number($credit_note->id);
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id']        = $this->input->get('customer_id');
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

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title']             = $title;
        $data['bodyclass']         = 'credit-note';
        $this->load->view('admin/credit_notes/credit_note', $data);
    }

    public function apply_credits_to_invoices($credit_note_id)
    {
        $creditApplied = false;
        if ($this->input->post()) {
            foreach ($this->input->post('amount') as $invoice_id=>$amount) {
                if ($this->credit_notes_model->apply_credits($credit_note_id, array('amount'=>$amount, 'invoice_id'=>$invoice_id))) {
                    update_invoice_status($invoice_id, true);
                    $creditsApplied = true;
                }
            }
        }
        if ($creditApplied) {
            set_alert('success', _l('credits_successfully_applied_to_invoices'));
        }
        redirect(admin_url('credit_notes/list_credit_notes/'.$credit_note_id));
    }

    public function credit_note_from_invoice($invoice_id)
    {
        if (has_permission('credit_notes', '', 'create')) {
            $id = $this->credit_notes_model->credit_note_from_invoice($invoice_id);

            if ($id) {
                redirect(admin_url('credit_notes/credit_note/'.$id));
            }
        }
        redirect(admin_url('invoices/list_invoices/'.$invoice_id));
    }

    /* Get all invoice note data */
    public function get_credit_note_data_ajax($id)
    {
        if (!has_permission('credit_notes', '', 'view') && !has_permission('credit_notes', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die(_l('credit_note_not_found'));
        }

        $credit_note = $this->credit_notes_model->get($id);

        if (!$credit_note || (!has_permission('credit_notes', '', 'view') && $credit_note->addedfrom != get_staff_user_id())) {
            echo _l('credit_note_not_found');
            die;
        }

        $template_name    = 'credit-note-send-to-client';
        $contact = $this->clients_model->get_contact(get_primary_contact_user_id($credit_note->clientid));

        $email   = '';
        if ($contact) {
            $email = $contact->email;
        }

        $data['template'] = get_email_template_for_sending($template_name, $email);

        $data['template_name']     = $template_name;
        $this->db->where('slug', $template_name);
        $this->db->where('language', 'english');
        $template_result = $this->db->get('tblemailtemplates')->row();

        $data['template_system_name'] = $template_result->name;
        $data['template_id'] = $template_result->emailtemplateid;

        $data['template_disabled'] = false;
        if (total_rows('tblemailtemplates', array('slug'=>$data['template_name'], 'active'=>0)) > 0) {
            $data['template_disabled'] = true;
        }

        $data['credit_note'] = $credit_note;
        $data['members']  = $this->staff_model->get('', 1);
        $data['available_creditable_invoices'] = $this->credit_notes_model->get_available_creditable_invoices($id);

        $this->load->view('admin/credit_notes/credit_note_preview_template', $data);
    }

    public function mark_open($id)
    {
        if (total_rows('tblcreditnotes', array('status'=>3, 'id'=>$id)) > 0 && has_permission('credit_notes', '', 'edit')) {
            $this->credit_notes_model->mark($id, 1);
        }

        redirect(admin_url('credit_notes/list_credit_notes/'.$id));
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->credit_notes_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function mark_void($id)
    {
        $credit_note = $this->credit_notes_model->get($id);
        if ($credit_note->status != 2 && $credit_note->status != 3 && !$credit_note->credits_used && has_permission('credit_notes', '', 'edit')) {
            $this->credit_notes_model->mark($id, 3);
        }
        redirect(admin_url('credit_notes/list_credit_notes/'.$id));
    }

    /* Send credit note to email */
    public function send_to_email($id)
    {
        if (!has_permission('credit_notes', '', 'view') && !has_permission('credit_notes', '', 'view_own')) {
            access_denied('credit_notes');
        }
        $success = $this->credit_notes_model->send_credit_note_to_client($id, $this->input->post('attach_pdf'), $this->input->post('cc'));
        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('credit_note_sent_to_client_success'));
        } else {
            set_alert('danger', _l('credit_note_sent_to_client_fail'));
        }
        redirect(admin_url('credit_notes/list_credit_notes/' . $id));
    }

    public function delete_invoice_applied_credit($id, $credit_id, $invoice_id)
    {
        if (has_permission('credit_notes', '', 'delete')) {
            $this->credit_notes_model->delete_applied_credit($id, $credit_id, $invoice_id);
        }
        redirect(admin_url('invoices/list_invoices/'.$invoice_id));
    }

    public function delete_credit_note_applied_credit($id, $credit_id, $invoice_id)
    {
        if (has_permission('credit_notes', '', 'delete')) {
            $this->credit_notes_model->delete_applied_credit($id, $credit_id, $invoice_id);
        }
        redirect(admin_url('credit_notes/list_credit_notes/'.$credit_id));
    }

    /* Delete credit note */
    public function delete($id)
    {
        if (!has_permission('credit_notes', '', 'delete')) {
            access_denied('credit_notes');
        }

        if (!$id) {
            redirect(admin_url('credit_notes'));
        }

        $credit_note = $this->credit_notes_model->get($id);

        if ($credit_note->credits_used || $credit_note->status == 2) {
            $success = false;
        } else {
            $success = $this->credit_notes_model->delete($id);
        }

        if ($success) {
            set_alert('success', _l('deleted', _l('credit_note')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('credit_note_lowercase')));
        }

        redirect(admin_url('credit_notes'));
    }

    /* Generates credit note PDF and send to email */
    public function pdf($id)
    {
        if (!has_permission('credit_notes', '', 'view') && !has_permission('credit_notes', '', 'view_own')) {
            access_denied('credit_notes');
        }
        if (!$id) {
            redirect(admin_url('credit_notes/list_credit_notes'));
        }
        $credit_note        = $this->credit_notes_model->get($id);
        $credit_note_number = format_credit_note_number($credit_note->id);

        try {
            $pdf            = credit_note_pdf($credit_note);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type           = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(mb_strtoupper(slug_it($credit_note_number)) . '.pdf', $type);
    }
}
