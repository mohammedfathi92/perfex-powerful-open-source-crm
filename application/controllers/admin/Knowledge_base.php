<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Knowledge_base extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('knowledge_base_model');
    }

    /* List all knowledgebase articles */
    public function index()
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        if ($this->input->is_ajax_request()) {
            $this->perfex_base->get_table_data('kb_articles');
        }
        $data['groups']    = $this->knowledge_base_model->get_kbg();
        $data['bodyclass'] = 'top-tabs kan-ban-body';
        $data['title']     = _l('kb_string');
        $this->load->view('admin/knowledge_base/articles', $data);
    }

    /* Add new article or edit existing*/
    public function article($id = '')
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        if ($this->input->post()) {
             $data                = $this->input->post();
             $data['description'] = $this->input->post('description', false);

            if ($id == '') {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }
                $id = $this->knowledge_base_model->add_article($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('kb_article')));
                    redirect(admin_url('knowledge_base/article/' . $id));
                }
            } else {
                if (!has_permission('knowledge_base', '', 'edit')) {
                    access_denied('knowledge_base');
                }
                $success = $this->knowledge_base_model->update_article($data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('kb_article')));
                }
                redirect(admin_url('knowledge_base/article/' . $id));
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('kb_article_lowercase'));
        } else {
            $article         = $this->knowledge_base_model->get($id);
            $data['article'] = $article;
            $title           = _l('edit', _l('kb_article')) . ' ' . $article->subject;
        }
        $data['bodyclass'] = 'kb-article';
        $data['title'] = $title;
        $this->load->view('admin/knowledge_base/article', $data);
    }

    public function view($slug)
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('View Knowledge Base Article');
        }

        $data['article'] = $this->knowledge_base_model->get(false, $slug);

        if (!$data['article']) {
            show_404();
        }

        $data['related_articles'] = $this->knowledge_base_model->get_related_articles($data['article']->articleid, false);

        add_views_tracking('kb_article', $data['article']->articleid);
        $data['title'] = $data['article']->subject;
        $this->load->view('admin/knowledge_base/view', $data);
    }

    public function add_kb_answer()
    {
        // This is for did you find this answer useful
        if (($this->input->post() && $this->input->is_ajax_request())) {
            echo json_encode($this->knowledge_base_model->add_article_answer($this->input->post()));
            die();
        }
    }

    /* Change article active or inactive */
    public function change_article_status($id, $status)
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->knowledge_base_model->change_article_status($id, $status);
            }
        }
    }

    public function update_kan_ban()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $success = $this->knowledge_base_model->update_kan_ban($this->input->post());
                $message = '';
                if ($success) {
                    $message = _l('updated_successfully', _l('kb_article'));
                }
                echo json_encode(array(
                    'success' => $success,
                    'message' => $message
                ));
                die();
            }
        }
    }

    public function change_group_color()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $this->knowledge_base_model->change_group_color($this->input->post());
            }
        }
    }

    /* Delete article from database */
    public function delete_article($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        if (!$id) {
            redirect(admin_url('knowledge_base'));
        }
        $response = $this->knowledge_base_model->delete_article($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('kb_article')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('kb_article_lowercase')));
        }
        redirect(admin_url('knowledge_base'));
    }

    /* View all article groups */
    public function manage_groups()
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        $data['groups'] = $this->knowledge_base_model->get_kbg();
        $data['title']  = _l('als_kb_groups');
        $this->load->view('admin/knowledge_base/manage_groups', $data);
    }

    /* Add or edit existing article group */
    public function group($id = '')
    {
        if (!has_permission('knowledge_base', '', 'view')) {
            access_denied('knowledge_base');
        }
        if ($this->input->post()) {
            $post_data = $this->input->post();
            $article_add_edit = isset($post_data['article_add_edit']);
            if(isset($post_data['article_add_edit'])) {
                unset($post_data['article_add_edit']);
            }
            if (!$this->input->post('id')) {
                if (!has_permission('knowledge_base', '', 'create')) {
                    access_denied('knowledge_base');
                }
                $id = $this->knowledge_base_model->add_group($post_data);
                if(!$article_add_edit && $id) {
                    set_alert('success', _l('added_successfully', _l('kb_dt_group_name')));
                } else {
                    echo json_encode(array(
                        'id'=>$id,
                        'success'=>$id ? true : false,
                        'name'=>$post_data['name'],
                    ));
                }
            } else {

                if (!has_permission('knowledge_base', '', 'edit')) {
                    access_denied('knowledge_base');
                }

                $id   = $post_data['id'];
                unset($post_data['id']);
                $success = $this->knowledge_base_model->update_group($post_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('kb_dt_group_name')));
                }
            }
            die;
        }
    }

    /* Change group active or inactive */
    public function change_group_status($id, $status)
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->knowledge_base_model->change_group_status($id, $status);
            }
        }
    }

    public function update_groups_order()
    {
        if (has_permission('knowledge_base', '', 'edit')) {
            if ($this->input->post()) {
                $this->knowledge_base_model->update_groups_order();
            }
        }
    }

    /* Delete article group */
    public function delete_group($id)
    {
        if (!has_permission('knowledge_base', '', 'delete')) {
            access_denied('knowledge_base');
        }
        if (!$id) {
            redirect(admin_url('knowledge_base/manage_groups'));
        }
        $response = $this->knowledge_base_model->delete_group($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('danger', _l('is_referenced', _l('kb_dt_group_name')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('kb_dt_group_name')));
        } else {
            set_alert('warning', _l('problem_deleting', mb_strtolower(_l('kb_dt_group_name'))));
        }
        redirect(admin_url('knowledge_base/manage_groups'));
    }

    public function get_article_by_id_ajax($id)
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->knowledge_base_model->get($id));
        }
    }
}
