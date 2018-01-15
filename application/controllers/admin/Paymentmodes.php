<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Paymentmodes extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('payment_modes_model');
        if (!is_admin()) {
            access_denied('Payment Modes');
        }
    }

    /* List all peyment modes*/
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('payment_modes');
        }
        $data['title'] = _l('payment_modes');
        $this->load->view('admin/paymentmodes/manage', $data);
    }

    /* Add or update payment mode / ajax */
    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['paymentmodeid'] == '') {
                $message = '';
                $success = $this->payment_modes_model->add($data);
                if ($success) {
                    $message = _l('added_successfully', _l('payment_mode'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
            } else {
                $message = '';
                $success = $this->payment_modes_model->edit($data);
                if ($success) {
                    $message = _l('updated_successfully', _l('payment_mode'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
            }
        }
    }

    /* Delete payment mode */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('paymentmodes'));
        }
        $response = $this->payment_modes_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('payment_mode_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('payment_mode')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_mode_lowercase')));
        }
        redirect(admin_url('paymentmodes'));
    }

    // Since version 1.0.1
    // Change payment mode active or inactive
    public function change_payment_mode_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->payment_modes_model->change_payment_mode_status($id, $status);
        }
    }

    // Since version 1.0.1
    // Change to show this mode to client or not
    public function change_payment_mode_show_to_client_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->payment_modes_model->change_payment_mode_show_to_client_status($id, $status);
        }
    }
}
