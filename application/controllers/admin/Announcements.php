<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Announcements extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('announcements_model');
    }

    /* List all announcements */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('announcements');
        }
        $data['title'] = _l('announcements');
        $this->load->view('admin/announcements/manage', $data);
    }

    /* Edit announcement or add new if passed id */
    public function announcement($id = '')
    {
        if (!is_admin()) {
            access_denied('Announcement');
        }
        if ($this->input->post()) {
            $data            = $this->input->post();
            $data['message'] = $this->input->post('message', false);
            if ($id == '') {
                $id = $this->announcements_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('announcement')));
                    redirect(admin_url('announcements/view/' . $id));
                }
            } else {
                $success = $this->announcements_model->update($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('announcement')));
                }
                redirect(admin_url('announcements/view/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('announcement_lowercase'));
        } else {
            $data['announcement'] = $this->announcements_model->get($id);
            $title                = _l('edit', _l('announcement_lowercase'));
        }
        $data['title'] = $title;
        $this->load->view('admin/announcements/announcement', $data);
    }

    public function view($id)
    {
        if (is_staff_member()) {
            $announcement = $this->announcements_model->get($id);
            if (!$announcement) {
                blank_page(_l('announcement_not_found'));
            }
            $data['announcement']         = $announcement;
            $data['recent_announcements'] = $this->announcements_model->get('', array(
                'announcementid !=' => $id
            ), 4);
            $data['title']                = $announcement->name;
            $this->load->view('admin/announcements/view', $data);
        }
    }

    /* Delete announcement from database */
    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('announcements'));
        }
        if (!is_admin()) {
            access_denied('Announcement');
        }
        $response = $this->announcements_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('announcement')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('announcement_lowercase')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }
}
