<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Taxes extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('taxes_model');
        if (!is_admin()) {
            access_denied('Taxes');
        }
    }

    /* List all taxes */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('taxes');
        }
        $data['title'] = _l('taxes');
        $this->load->view('admin/taxes/manage', $data);
    }

    /* Add or edit tax / ajax */
    public function manage()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['taxid'] == '') {
                $success = $this->taxes_model->add($data);
                $message = '';
                if ($success == true) {
                    $message = _l('added_successfully', _l('tax'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
            } else {
                $success = $this->taxes_model->edit($data);
                $message = '';
                if (is_array($success) && isset($success['tax_is_using_expenses'])) {
                    $success = false;
                    $message = _l('tax_is_used_in_expenses_warning');
                } elseif ($success == true) {
                    $message = _l('updated_successfully', _l('tax'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
            }
        }
    }

    /* Delete tax from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('taxes'));
        }
        $response = $this->taxes_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('tax_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('tax')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('tax_lowercase')));
        }
        redirect(admin_url('taxes'));
    }

    public function tax_name_exists()
    {
        if ($this->input->post()) {
            $tax_id = $this->input->post('taxid');
            if ($tax_id != '') {
                $this->db->where('id', $tax_id);
                $_current_tax = $this->db->get('tbltaxes')->row();
                if ($_current_tax->name == $this->input->post('name')) {
                    echo json_encode(true);
                    die();
                }
            }
            $this->db->where('name', $this->input->post('name'));
            $total_rows = $this->db->count_all_results('tbltaxes');
            if ($total_rows > 0) {
                echo json_encode(false);
            } else {
                echo json_encode(true);
            }
            die();
        }
    }
}
