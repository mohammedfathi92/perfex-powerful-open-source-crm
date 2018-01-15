<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Todo extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('todo_model');
    }

    /* Get all staff todo items */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->todo_model->get_todo_items($this->input->post('finished'), $this->input->post('todo_page')));
            exit;
        }
        $data['bodyclass']              = 'main_todo_page';
        $data['total_pages_finished']   = ceil(total_rows('tbltodoitems', array(
            'finished' => 1,
            'staffid' => get_staff_user_id()
        )) / $this->todo_model->getTodosLimit());
        $data['total_pages_unfinished'] = ceil(total_rows('tbltodoitems', array(
            'finished' => 0,
            'staffid' => get_staff_user_id()
        )) / $this->todo_model->getTodosLimit());
        $data['title']                  = _l('my_todos');
        $this->load->view('admin/todos/all', $data);
    }

    /* Add new todo item */
    public function todo()
    {
        if ($this->input->post()) {
            $data = $this->input->post();
            if ($data['todoid'] == '') {
                unset($data['todoid']);
                $id = $this->todo_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('todo')));
                }
            } else {
                $id = $data['todoid'];
                unset($data['todoid']);
                $success = $this->todo_model->update($id, $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('todo')));
                }
            }

            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function get_by_id($id)
    {
        $todo              = $this->todo_model->get($id);
        $todo->description = clear_textarea_breaks($todo->description);
        echo json_encode($todo);
    }

    /* Change todo status */
    public function change_todo_status($id, $status)
    {
        $success = $this->todo_model->change_todo_status($id, $status);
        if ($success) {
            set_alert('success', _l('todo_status_changed'));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    /* Update todo order / ajax */
    public function update_todo_items_order()
    {
        if ($this->input->post()) {
            $this->todo_model->update_todo_items_order($this->input->post());
        }
    }

    /* Delete todo item from databse */
    public function delete_todo_item($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode(array(
                'success' => $this->todo_model->delete_todo_item($id)
            ));
        }
        die();
    }
}
