<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Roles extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        // Model is autoloaded
    }

    /* List all staff roles */
    public function index()
    {
        if (!has_permission('roles', '', 'view')) {
            access_denied('roles');
        }
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('roles');
        }
        $data['title'] = _l('all_roles');
        $this->load->view('admin/roles/manage', $data);
    }

    /* Add new role or edit existing one */
    public function role($id = '')
    {
        if (!has_permission('roles', '', 'view')) {
            access_denied('roles');
        }
        if ($this->input->post()) {
            if ($id == '') {
                if (!has_permission('roles', '', 'create')) {
                    access_denied('roles');
                }
                $id = $this->roles_model->add($this->input->post());
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('role')));
                    redirect(admin_url('roles/role/' . $id));
                }
            } else {
                if (!has_permission('roles', '', 'edit')) {
                    access_denied('roles');
                }
                $success = $this->roles_model->update($this->input->post(), $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('role')));
                }
                redirect(admin_url('roles/role/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('role_lowercase'));
        } else {
            $data['role_permissions'] = $this->roles_model->get_role_permissions($id);
            $role                     = $this->roles_model->get($id);
            $data['role']             = $role;
            $title                    = _l('edit', _l('role_lowercase')) . ' ' . $role->name;
        }
        $data['permissions'] = $this->roles_model->get_permissions();
        $data['title']       = $title;
        $this->load->view('admin/roles/role', $data);
    }

    /* Delete staff role from database */
    public function delete($id)
    {
        if (!has_permission('roles', '', 'delete')) {
            access_denied('roles');
        }
        if (!$id) {
            redirect(admin_url('roles'));
        }
        $response = $this->roles_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('role_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('role')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('role_lowercase')));
        }
        redirect(admin_url('roles'));
    }
}
