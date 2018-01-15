<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Goals extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('goals_model');
    }

    /* List all announcements */
    public function index()
    {
        if (!has_permission('goals', '', 'view')) {
            access_denied('goals');
        }
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('goals');
        }
        $data['circle_progress_asset'] = true;
        $data['title']                 = _l('goals_tracking');
        $this->load->view('admin/goals/manage', $data);
    }

    public function goal($id = '')
    {
        if (!has_permission('goals', '', 'view')) {
            access_denied('goals');
        }
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('goals', '', 'create')) {
                    access_denied('goals');
                }
                $id = $this->goals_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('goal')));
                    redirect(admin_url('goals/goal/' . $id));
                }
            } else {
                if (!has_permission('goals', '', 'edit')) {
                    access_denied('goals');
                }
                $success = $this->goals_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('goal')));
                }
                redirect(admin_url('goals/goal/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('goal_lowercase'));
        } else {
            $data['goal']        = $this->goals_model->get($id);
            $data['achievement'] = $this->goals_model->calculate_goal_achievement($id);

            $title               = _l('edit', _l('goal_lowercase'));
        }

        $this->load->model('staff_model');
        $data['members'] = $this->staff_model->get('', 1, array('is_not_staff' => 0));

        $this->load->model('contracts_model');
        $data['contract_types']        = $this->contracts_model->get_contract_types();
        $data['title']                 = $title;
        $data['circle_progress_asset'] = true;
        $this->load->view('admin/goals/goal', $data);
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!has_permission('goals', '', 'delete')) {
            access_denied('goals');
        }
        if (!$id) {
            redirect(admin_url('goals'));
        }
        $response = $this->goals_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('goal')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('goal_lowercase')));
        }
        redirect(admin_url('goals'));
    }

    public function notify($id, $notify_type)
    {
        if (!has_permission('goals', '', 'edit') && !has_permission('goals', '', 'create')) {
            access_denied('goals');
        }
        if (!$id) {
            redirect(admin_url('goals'));
        }
        $success = $this->goals_model->notify_staff_members($id, $notify_type);
        if ($success) {
            set_alert('success', _l('goal_notify_staff_notified_manually_success'));
        } else {
            set_alert('warning', _l('goal_notify_staff_notified_manually_fail'));
        }
        redirect(admin_url('goals/goal/' . $id));
    }
}
