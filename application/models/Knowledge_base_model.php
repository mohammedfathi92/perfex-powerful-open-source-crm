<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Knowledge_base_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get article by id
     * @param  string $id   article ID
     * @param  string $slug if search by slug
     * @return mixed       if ID or slug passed return object else array
     */
    public function get($id = '', $slug = '')
    {
        $this->db->select('slug,articleid, articlegroup, subject,tblknowledgebase.description,tblknowledgebase.active as active_article,tblknowledgebasegroups.active as active_group,name as group_name,staff_article');
        $this->db->from('tblknowledgebase');
        $this->db->join('tblknowledgebasegroups', 'tblknowledgebasegroups.groupid = tblknowledgebase.articlegroup', 'left');
        $this->db->order_by('article_order', 'asc');
        if (is_numeric($id)) {
            $this->db->where('articleid', $id);
        }
        if ($slug != '') {
            $this->db->where('slug', $slug);
        }
        if ($this->input->get('groupid')) {
            $this->db->where('articlegroup', $this->input->get('groupid'));
        }
        if (is_numeric($id) || $slug != '') {
            return $this->db->get()->row();
        } else {
            return $this->db->get()->result_array();
        }
    }

    /**
     * Get related artices based on article id
     * @param  mixed $current_id current article id
     * @return array
     */
    public function get_related_articles($current_id, $customers = true)
    {
        $total_related_articles = 5;
        $total_related_articles = do_action('total_related_articles', $total_related_articles);

        $this->db->select('articlegroup');
        $this->db->where('articleid', $current_id);
        $article = $this->db->get('tblknowledgebase')->row();

        $this->db->where('articlegroup', $article->articlegroup);
        $this->db->where('articleid !=', $current_id);
        $this->db->where('active', 1);
        if ($customers == true) {
            $this->db->where('staff_article', 0);
        } else {
            $this->db->where('staff_article', 1);
        }
        $this->db->limit($total_related_articles);

        return $this->db->get('tblknowledgebase')->result_array();
    }

    /**
     * Add new article
     * @param array $data article data
     */
    public function add_article($data)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        if (isset($data['staff_article'])) {
            $data['staff_article'] = 1;
        } else {
            $data['staff_article'] = 0;
        }
        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['slug']        = slug_it($data['subject']);
        $this->db->like('slug', $data['slug']);
        $slug_total = $this->db->count_all_results('tblknowledgebase');
        if ($slug_total > 0) {
            $data['slug'] .= '-' . ($slug_total + 1);
        }

        $data = do_action('before_add_kb_article', $data);

        $this->db->insert('tblknowledgebase', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Article Added [ArticleID: ' . $insert_id . ' GroupID: ' . $data['articlegroup'] . ']');
        }

        return $insert_id;
    }

    /**
     * Update article
     * @param  array $data article data
     * @param  mixed $id   articleid
     * @return boolean
     */
    public function update_article($data, $id)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }

        if (isset($data['staff_article'])) {
            $data['staff_article'] = 1;
        } else {
            $data['staff_article'] = 0;
        }

        $this->db->where('articleid', $id);
        $this->db->update('tblknowledgebase', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Article Updated [ArticleID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function update_kan_ban($data)
    {
        $affectedRows = 0;
        foreach ($data['order'] as $o) {
            $this->db->where('articleid', $o[0]);
            $this->db->update('tblknowledgebase', array(
                'article_order' => $o[1],
                'articlegroup' => $data['groupid']
            ));
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Change article status
     * @param  mixed $id     article id
     * @param  boolean $status is active or not
     */
    public function change_article_status($id, $status)
    {
        $this->db->where('articleid', $id);
        $this->db->update('tblknowledgebase', array(
            'active' => $status
        ));
        logActivity('Article Status Changed [ArticleID: ' . $id . ' Status: ' . $status . ']');
    }

    public function update_groups_order()
    {
        $data = $this->input->post();
        foreach ($data['order'] as $group) {
            $this->db->where('groupid', $group[0]);
            $this->db->update('tblknowledgebasegroups', array(
                'group_order' => $group[1]
            ));
        }
    }

    /**
     * Delete article from database and all article connections
     * @param  mixed $id article ID
     * @return boolean
     */
    public function delete_article($id)
    {
        $this->db->where('articleid', $id);
        $this->db->delete('tblknowledgebase');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('articleid', $id);
            $this->db->delete('tblknowledgebasearticleanswers');

            $this->db->where('rel_type', 'kb_article');
            $this->db->where('rel_id', $id);
            $this->db->delete('tblviewstracking');

            logActivity('Article Deleted [ArticleID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get all KGB (Knowledge base groups)
     * @param  mixed $id Optional - KB Group
     * @param  mixed $active Optional - actve groups or not
     * @return mixed      array if not id passed else object
     */
    public function get_kbg($id = '', $active = '')
    {
        if (is_numeric($active)) {
            $this->db->where('active', $active);
        }
        if (is_numeric($id)) {
            $this->db->where('groupid', $id);

            return $this->db->get('tblknowledgebasegroups')->row();
        }
        $this->db->order_by('group_order', 'asc');

        return $this->db->get('tblknowledgebasegroups')->result_array();
    }

    /**
     * Add new knowledge base group/folder
     * @param array $data group data
     * @return boolean
     */
    public function add_group($data)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        $this->db->insert('tblknowledgebasegroups', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Article Group Added [GroupID: ' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Get knowledge base group by id
     * @param  mixed $id groupid
     * @return object
     */
    public function get_kbg_by_id($id)
    {
        $this->db->where('groupid', $id);

        return $this->db->get('tblknowledgebasegroups')->row();
    }

    /**
     * Update knowledge base group
     * @param  array $data group data
     * @param  mixed $id   groupid
     * @return boolean
     */
    public function update_group($data, $id)
    {
        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        $this->db->where('groupid', $id);
        $this->db->update('tblknowledgebasegroups', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Article Group Updated [GroupID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Change group status
     * @param  mixed $id     groupid id
     * @param  boolean $status is active or not
     */
    public function change_group_status($id, $status)
    {
        $this->db->where('groupid', $id);
        $this->db->update('tblknowledgebasegroups', array(
            'active' => $status
        ));
        logActivity('Article Status Changed [GroupID: ' . $id . ' Status: ' . $status . ']');
    }

    public function change_group_color($data)
    {
        $this->db->where('groupid', $data['group_id']);
        $this->db->update('tblknowledgebasegroups', array(
            'color' => $data['color']
        ));
    }

    /**
     * Delete knowledge base article
     * @param  mixed $id groupid
     * @return boolean
     */
    public function delete_group($id)
    {
        $current = $this->get_kbg_by_id($id);
        // Check if group already is using
        if (is_reference_in_table('articlegroup', 'tblknowledgebase', $id)) {
            return array(
                'referenced' => true
            );
        }
        $this->db->where('groupid', $id);
        $this->db->delete('tblknowledgebasegroups');
        if ($this->db->affected_rows() > 0) {
            logActivity('Knowledge Base Group Deleted');

            return true;
        }

        return false;
    }

    /**
     * Add new article vote / Called from client area
     * @param array $data article data
     * @return mixed
     */
    public function add_article_answer($data)
    {
        $articleid = $this->input->post('articleid');
        $ip        = $this->input->ip_address();
        $this->db->where('ip', $ip)->where('articleid', $articleid)->order_by('date', 'desc')->limit(1);
        $answer = $this->db->get('tblknowledgebasearticleanswers')->row();
        if ($answer) {
            $last_answer    = strtotime($answer->date);
            $minus_24_hours = strtotime('-24 hours');
            if ($last_answer >= $minus_24_hours) {
                return array(
                    'success' => false,
                    'message' => _l('clients_article_only_1_vote_today')
                );
            }
        }
        $data['answer']    = $data['answer'];
        $data['ip']        = $ip;
        $data['date']      = date('Y-m-d H:i:s');
        $data['articleid'] = $articleid;
        $this->db->insert('tblknowledgebasearticleanswers', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return array(
                'success' => true,
                'message' => _l('clients_article_voted_thanks_for_feedback')
            );
        }

        return array(
            'success' => false
        );
    }
}
