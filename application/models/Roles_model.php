<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Roles_model extends CRM_Model
{
    private $perm_statements = array('view', 'view_own', 'edit', 'create', 'delete');

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Add new employee role
     * @param mixed $data
     */
    public function add($data)
    {
        $permissions = array();
        if (isset($data['view'])) {
            $permissions['view'] = $data['view'];
            unset($data['view']);
        }

        if (isset($data['view_own'])) {
            $permissions['view_own'] = $data['view_own'];
            unset($data['view_own']);
        }
        if (isset($data['edit'])) {
            $permissions['edit'] = $data['edit'];
            unset($data['edit']);
        }
        if (isset($data['create'])) {
            $permissions['create'] = $data['create'];
            unset($data['create']);
        }
        if (isset($data['delete'])) {
            $permissions['delete'] = $data['delete'];
            unset($data['delete']);
        }

        $this->db->insert('tblroles', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $_all_permissions = $this->roles_model->get_permissions();
            foreach ($_all_permissions as $permission) {
                $this->db->insert('tblrolepermissions', array(
                    'permissionid' => $permission['permissionid'],
                    'roleid' => $insert_id,
                    'can_view' => 0,
                    'can_view_own' => 0,
                    'can_edit' => 0,
                    'can_create' => 0,
                    'can_delete' => 0
                ));
            }

            foreach ($this->perm_statements as $c) {
                foreach ($permissions as $key => $p) {
                    if ($key == $c) {
                        foreach ($p as $perm) {
                            $this->db->where('roleid', $insert_id);
                            $this->db->where('permissionid', $perm);
                            $this->db->update('tblrolepermissions', array(
                                'can_' . $c => 1
                            ));
                        }
                    }
                }
            }

            logActivity('New Role Added [ID: ' . $insert_id . '.' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    /**
     * Update employee role
     * @param  array $data role data
     * @param  mixed $id   role id
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;
        $permissions  = array();
        if (isset($data['view'])) {
            $permissions['view'] = $data['view'];
            unset($data['view']);
        }

        if (isset($data['view_own'])) {
            $permissions['view_own'] = $data['view_own'];
            unset($data['view_own']);
        }
        if (isset($data['edit'])) {
            $permissions['edit'] = $data['edit'];
            unset($data['edit']);
        }
        if (isset($data['create'])) {
            $permissions['create'] = $data['create'];
            unset($data['create']);
        }
        if (isset($data['delete'])) {
            $permissions['delete'] = $data['delete'];
            unset($data['delete']);
        }
        $update_staff_permissions = false;
        if (isset($data['update_staff_permissions'])) {
            $update_staff_permissions = true;
            unset($data['update_staff_permissions']);
        }
        $this->db->where('roleid', $id);
        $this->db->update('tblroles', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }


        $all_permissions = $this->roles_model->get_permissions();
        if (total_rows('tblrolepermissions', array(
            'roleid' => $id
        )) == 0) {
            foreach ($all_permissions as $p) {
                $_ins                 = array();
                $_ins['roleid']       = $id;
                $_ins['permissionid'] = $p['permissionid'];
                $this->db->insert('tblrolepermissions', $_ins);
            }
        } elseif (total_rows('tblrolepermissions', array(
                'roleid' => $id
            )) != count($all_permissions)) {
            foreach ($all_permissions as $p) {
                if (total_rows('tblrolepermissions', array(
                    'roleid' => $id,
                    'permissionid' => $p['permissionid']
                )) == 0) {
                    $_ins                 = array();
                    $_ins['roleid']       = $id;
                    $_ins['permissionid'] = $p['permissionid'];
                    $this->db->insert('tblrolepermissions', $_ins);
                }
            }
        }

        $_permission_restore_affected_rows = 0;
        foreach ($all_permissions as $permission) {
            foreach ($this->perm_statements as $c) {
                $this->db->where('roleid', $id);
                $this->db->where('permissionid', $permission['permissionid']);
                $this->db->update('tblrolepermissions', array(
                    'can_' . $c => 0
                ));
                if ($this->db->affected_rows() > 0) {
                    $_permission_restore_affected_rows++;
                }
            }
        }

        $_new_permissions_added_affected_rows = 0;
        foreach ($permissions as $key => $val) {
            foreach ($val as $p) {
                $this->db->where('roleid', $id);
                $this->db->where('permissionid', $p);
                $this->db->update('tblrolepermissions', array(
                    'can_' . $key => 1
                ));
                if ($this->db->affected_rows() > 0) {
                    $_new_permissions_added_affected_rows++;
                }
            }
        }
        if ($_new_permissions_added_affected_rows != $_permission_restore_affected_rows) {
            $affectedRows++;
        }

        if ($update_staff_permissions == true) {
            $this->load->model('staff_model');
            $staff = $this->staff_model->get('', '', array(
                'role' => $id
            ));
            foreach ($staff as $m) {
                if ($this->staff_model->update_permissions($permissions, $m['staffid'])) {
                    $affectedRows++;
                }
            }
        }

        if ($affectedRows > 0) {
            logActivity('Role Updated [ID: ' . $id . '.' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    /**
     * Get employee role by id
     * @param  mixed $id Optional role id
     * @return mixed     array if not id passed else object
     */
    public function get($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('roleid', $id);

            return $this->db->get('tblroles')->row();
        }

        return $this->db->get('tblroles')->result_array();
    }

    /**
     * Delete employee role
     * @param  mixed $id role id
     * @return mixed
     */
    public function delete($id)
    {
        $current = $this->get($id);
        // Check first if role is used in table
        if (is_reference_in_table('role', 'tblstaff', $id)) {
            return array(
                'referenced' => true
            );
        }
        $affectedRows = 0;
        $this->db->where('roleid', $id);
        $this->db->delete('tblroles');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        $this->db->where('roleid', $id);
        $this->db->delete('tblrolepermissions');
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            logActivity('Role Deleted [ID: ' . $id);

            return true;
        }

        return false;
    }

    /**
     * Get employee role permissions
     * @param  mixed $id permission id
     * @return mixed if id passed return object else array
     */
    public function get_permissions($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('permissionid', $id);

            return $this->db->get('tblpermissions')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get('tblpermissions')->result_array();
    }

    /**
     * Get specific role permissions
     * @param  mixed $id role id
     * @return array
     */
    public function get_role_permissions($id)
    {
        $this->db->where('roleid', $id);
        $this->db->join('tblpermissions', 'tblpermissions.permissionid = tblrolepermissions.permissionid', 'left');

        return $this->db->get('tblrolepermissions')->result_array();
    }

    /**
     * Get staff permission / Staff can have other permissions too different from the role which is assigned
     * @param  mixed $id Optional - staff id
     * @return array
     */
    public function get_staff_permissions($id = '')
    {
        // If not id is passed get from current user
        if ($id == false) {
            $id = get_staff_user_id();
        }
        $this->db->where('staffid', $id);

        return $this->db->get('tblstaffpermissions')->result_array();
    }

    public function get_contact_permissions($id)
    {
        $this->db->where('userid', $id);

        return $this->db->get('tblcontactpermissions')->result_array();
    }
}
