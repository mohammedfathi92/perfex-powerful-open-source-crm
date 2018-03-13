<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Custom_fields extends Admin_controller
{
    private $pdf_fields = array();
    private $client_portal_fields = array();
    private $client_editable_fields = array();

    public function __construct()
    {
        parent::__construct();
        $this->load->model('custom_fields_model');
        if (!is_admin()) {
            access_denied('Access Custom Fields');
        }
        // Add the pdf allowed fields
        $this->pdf_fields           = $this->custom_fields_model->get_pdf_allowed_fields();
        $this->client_portal_fields = $this->custom_fields_model->get_client_portal_allowed_fields();
        $this->client_editable_fields = $this->custom_fields_model->get_client_editable_fields();
    }

    /* List all custom fields */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('custom_fields');
        }
        $data['title'] = _l('custom_fields');
        $this->load->view('admin/custom_fields/manage', $data);
    }

    public function field($id = '')
    {
        if ($this->input->post()) {
            if ($id == '') {
                $id = $this->custom_fields_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('custom_field')));
                    redirect(admin_url('custom_fields/field/' . $id));
                }
            } else {
                $success = $this->custom_fields_model->update($this->input->post(), $id);
                if (is_array($success) && isset($success['cant_change_option_custom_field'])) {
                    set_alert('warning', _l('cf_option_in_use'));
                } elseif ($success === true) {
                    set_alert('success', _l('updated_successfully', _l('custom_field')));
                }
                redirect(admin_url('custom_fields/field/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('custom_field_lowercase'));
        } else {
            $data['custom_field'] = $this->custom_fields_model->get($id);
            $title                = _l('edit', _l('custom_field_lowercase'));
        }
        $data['pdf_fields']           = $this->pdf_fields;
        $data['client_portal_fields'] = $this->client_portal_fields;
        $data['client_editable_fields'] = $this->client_editable_fields;
        $data['title']                = $title;
        $this->load->view('admin/custom_fields/customfield', $data);
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('custom_fields'));
        }
        $response = $this->custom_fields_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('custom_field')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('custom_field_lowercase')));
        }
        redirect(admin_url('custom_fields'));
    }

    /* Change survey status active or inactive*/
    public function change_custom_field_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->custom_fields_model->change_custom_field_status($id, $status);
        }
    }
}
