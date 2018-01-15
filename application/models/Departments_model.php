<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Departments_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param  integer ID (optional)
     * @param  boolean (optional)
     * @return mixed
     * Get department object based on passed id if not passed id return array of all departments
     * Second parameter is to check if the request is coming from clientarea, so if any departments are hidden from client to exclude
     */
    public function get($id = false, $clientarea = false)
    {
        if ($clientarea == true) {
            $this->db->where('hidefromclient', 0);
        }
        if (is_numeric($id)) {
            $this->db->where('departmentid', $id);

            return $this->db->get('tbldepartments')->row();
        }

        $departments = $this->object_cache->get('departments');

        if(!$departments && !is_array($departments)) {
            $departments = $this->db->get('tbldepartments')->result_array();
            $this->object_cache->add('departments',$departments);
        }

        return $departments;
    }

    /**
     * @param array $_POST data
     * @return integer
     * Add new department
     */
    public function add($data)
    {
        if (isset($data['hidefromclient'])) {
            $data['hidefromclient'] = 1;
        } else {
            $data['hidefromclient'] = 0;
        }

        if (!empty($data['password'])) {
            $data['password'] = $this->encryption->encrypt($data['password']);
        }

        if (!isset($data['encryption'])) {
            $data['encryption'] = '';
        }

        if (!isset($data['delete_after_import'])) {
            $data['delete_after_import'] = 0;
        } else {
            $data['delete_after_import'] = 1;
        }

        $data = do_action('before_department_added', $data);
        $this->db->insert('tbldepartments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            do_action('after_department_added', $insert_id);
            logActivity('New Department Added [' . $data['name'] . ', ID: ' . $insert_id . ']');
        }

        return $insert_id;
    }

    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update department to database
     */
    public function update($data, $id)
    {
        $dep_original = $this->get($id);
        if (!$dep_original) {
            return false;
        }
        $hook_data['data'] = $data;
        $hook_data['id']   = $id;
        $hook_data         = do_action('before_department_updated', $hook_data);
        $data              = $hook_data['data'];
        $id                = $hook_data['id'];

        if (!isset($data['encryption'])) {
            $data['encryption'] = '';
        }

        if (!isset($data['delete_after_import'])) {
            $data['delete_after_import'] = 0;
        } else {
            $data['delete_after_import'] = 1;
        }

        if ($data['email'] == '') {
            $data['email'] = null;
        }
        if (isset($data['hidefromclient'])) {
            $data['hidefromclient'] = 1;
        } else {
            $data['hidefromclient'] = 0;
        }
        // Check if not empty $data['password']
        // Get original
        // Decrypt original
        // Compare with $data['password']
        // If equal unset
        // If not encrypt and save
        if (!empty($data['password'])) {
            $or_decrypted = $this->encryption->decrypt($dep_original->password);
            if ($or_decrypted == $data['password']) {
                unset($data['password']);
            } else {
                $data['password'] = $this->encryption->encrypt($data['password']);
            }
        }

        $this->db->where('departmentid', $id);
        $this->db->update('tbldepartments', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Department Updated [Name: ' . $data['name'] . ', ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete department from database, if used return array with key referenced
     */
    public function delete($id)
    {
        $id      = do_action('before_delete_department', $id);
        $current = $this->get($id);
        if (is_reference_in_table('department', 'tbltickets', $id)) {
            return array(
                'referenced' => true
            );
        }
        do_action('before_department_deleted', $id);
        $this->db->where('departmentid', $id);
        $this->db->delete('tbldepartments');
        if ($this->db->affected_rows() > 0) {
            logActivity('Department Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID (option)
     * @param  boolean (optional)
     * @return mixed
     * Get departments where staff belongs
     * If $onlyids passed return only departmentsID (simple array) if not returns array of all departments
     */
    public function get_staff_departments($userid = false, $onlyids = false)
    {
        if ($userid == false) {
            $userid = get_staff_user_id();
        }
        if ($onlyids == false) {
            $this->db->select();
        } else {
            $this->db->select('tblstaffdepartments.departmentid');
        }
        $this->db->from('tblstaffdepartments');
        $this->db->join('tbldepartments', 'tblstaffdepartments.departmentid = tbldepartments.departmentid', 'left');
        $this->db->where('staffid', $userid);
        $departments = $this->db->get()->result_array();
        if ($onlyids == true) {
            $departmentsid = array();
            foreach ($departments as $department) {
                array_push($departmentsid, $department['departmentid']);
            }

            return $departmentsid;
        }

        return $departments;
    }
}
