<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Staff_model extends CRM_Model
{
    private $perm_statements = array('view', 'view_own', 'edit', 'create', 'delete');

    public function __construct()
    {
        parent::__construct();
    }

    public function delete($id, $transfer_data_to)
    {
        if (!is_numeric($transfer_data_to)) {
            return false;
        }

        if ($id == $transfer_data_to) {
            return false;
        }

        do_action('before_delete_staff_member', array(
            'id' => $id,
            'transfer_data_to' => $transfer_data_to,
        ));

        $name          = get_staff_full_name($id);
        $transferred_to = get_staff_full_name($transfer_data_to);

        $this->db->where('addedfrom', $id);
        $this->db->update('tblestimates', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('sale_agent', $id);
        $this->db->update('tblestimates', array(
            'sale_agent' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblinvoices', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('sale_agent', $id);
        $this->db->update('tblinvoices', array(
            'sale_agent' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblexpenses', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblnotes', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('userid', $id);
        $this->db->update('tblpostcomments', array(
            'userid' => $transfer_data_to,
        ));

        $this->db->where('creator', $id);
        $this->db->update('tblposts', array(
            'creator' => $transfer_data_to,
        ));

        $this->db->where('staff_id', $id);
        $this->db->update('tblprojectdiscussions', array(
            'staff_id' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblprojects', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblcreditnotes', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('staff_id', $id);
        $this->db->update('tblcredits', array(
            'staff_id' => $transfer_data_to,
        ));

        $this->db->where('staff_id', $id);
        $this->db->update('tblgoals', array(
            'staff_id' => $transfer_data_to,
        ));

        $this->db->where('staffid', $id);
        $this->db->update('tblprojectfiles', array(
            'staffid' => $transfer_data_to,
        ));

        $this->db->where('staffid', $id);
        $this->db->update('tblproposalcomments', array(
            'staffid' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblproposals', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('staffid', $id);
        $this->db->update('tblstafftaskcomments', array(
            'staffid' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->where('is_added_from_contact', 0);
        $this->db->update('tblstafftasks', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('staffid', $id);
        $this->db->update('tblfiles', array(
            'staffid' => $transfer_data_to,
        ));

        $this->db->where('renewed_by_staff_id', $id);
        $this->db->update('tblcontractrenewals', array(
            'renewed_by_staff_id' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tbltaskchecklists', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('finished_from', $id);
        $this->db->update('tbltaskchecklists', array(
            'finished_from' => $transfer_data_to,
        ));

        $this->db->where('admin', $id);
        $this->db->update('tblticketreplies', array(
            'admin' => $transfer_data_to,
        ));

        $this->db->where('admin', $id);
        $this->db->update('tbltickets', array(
            'admin' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblleads', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('assigned', $id);
        $this->db->update('tblleads', array(
            'assigned' => $transfer_data_to,
        ));

        $this->db->where('staff_id', $id);
        $this->db->update('tbltaskstimers', array(
            'staff_id' => $transfer_data_to,
        ));

        $this->db->where('addedfrom', $id);
        $this->db->update('tblcontracts', array(
            'addedfrom' => $transfer_data_to,
        ));

        $this->db->where('assigned_from', $id);
        $this->db->where('is_assigned_from_contact', 0);
        $this->db->update('tblstafftaskassignees', array(
            'assigned_from' => $transfer_data_to,
        ));

        $this->db->where('responsible', $id);
        $this->db->update('tblleadsintegration', array(
            'responsible' => $transfer_data_to,
        ));

        $this->db->where('responsible', $id);
        $this->db->update('tblwebtolead', array(
            'responsible' => $transfer_data_to,
        ));

        $this->db->where('notify_type', 'specific_staff');
        $webtolead = $this->db->get('tblwebtolead')->result_array();

        foreach ($webtolead as $form) {
            if (!empty($form['notify_ids'])) {
                $staff = unserialize($form['notify_ids']);
                if (is_array($staff)) {
                    if (in_array($id, $staff)) {
                        if (($key = array_search($id, $staff)) !== false) {
                            unset($staff[$key]);
                            $staff = serialize(array_values($staff));
                            $this->db->where('id', $form['id']);
                            $this->db->update('tblwebtolead', array(
                                'notify_ids' => $staff,
                            ));
                        }
                    }
                }
            }
        }

        $this->db->where('id', 1);
        $leads_email_integration = $this->db->get('tblleadsintegration')->row();

        if ($leads_email_integration->notify_type == 'specific_staff') {
            if (!empty($leads_email_integration->notify_ids)) {
                $staff = unserialize($leads_email_integration->notify_ids);
                if (is_array($staff)) {
                    if (in_array($id, $staff)) {
                        if (($key = array_search($id, $staff)) !== false) {
                            unset($staff[$key]);
                            $staff = serialize(array_values($staff));
                            $this->db->where('id', 1);
                            $this->db->update('tblleadsintegration', array(
                                'notify_ids' => $staff,
                            ));
                        }
                    }
                }
            }
        }

        $this->db->where('assigned', $id);
        $this->db->update('tbltickets', array(
            'assigned' => 0,
        ));

        $this->db->where('staff', 1);
        $this->db->where('userid', $id);
        $this->db->delete('tbldismissedannouncements');

        $this->db->where('userid', $id);
        $this->db->delete('tblcommentlikes');

        $this->db->where('userid', $id);
        $this->db->delete('tblpostlikes');

        $this->db->where('staff_id', $id);
        $this->db->delete('tblcustomeradmins');

        $this->db->where('fieldto', 'staff');
        $this->db->where('relid', $id);
        $this->db->delete('tblcustomfieldsvalues');

        $this->db->where('userid', $id);
        $this->db->delete('tblevents');

        $this->db->where('touserid', $id);
        $this->db->delete('tblnotifications');

        $this->db->where('staff_id', $id);
        $this->db->delete('tblprojectmembers');

        $this->db->where('staff_id', $id);
        $this->db->delete('tblprojectnotes');

        $this->db->where('creator', $id);
        $this->db->or_where('staff', $id);
        $this->db->delete('tblreminders');

        $this->db->where('staffid', $id);
        $this->db->delete('tblstaffdepartments');

        $this->db->where('staffid', $id);
        $this->db->delete('tbltodoitems');

        $this->db->where('staff', 1);
        $this->db->where('user_id', $id);
        $this->db->delete('tbluserautologin');

        $this->db->where('staffid', $id);
        $this->db->delete('tblstaffpermissions');

        $this->db->where('staffid', $id);
        $this->db->delete('tblstafftaskassignees');

        $this->db->where('staffid', $id);
        $this->db->delete('tblstafftasksfollowers');

        $this->db->where('staff_id', $id);
        $this->db->delete('tblpinnedprojects');

        $this->db->where('staffid', $id);
        $this->db->delete('tblstaff');
        logActivity('Staff Member Deleted [Name: ' . $name . ', Data Transferred To: ' . $transferred_to . ']');
        do_action('staff_member_deleted', array(
            'id' => $id,
            'transfer_data_to' => $transfer_data_to,
        ));

        return true;
    }

    /**
     * Get staff member/s
     * @param  mixed $id Optional - staff id
     * @param  integer $active Optional get all active or inactive
     * @return mixed if id is passed return object else array
     */
    public function get($id = '', $active = '', $where = array())
    {
        $select_str = '*,CONCAT(firstname," ",lastname) as full_name';

        // Used to prevent multiple queries on logged in staff to check the total unread notifications in admin_controller.php
        if(is_staff_logged_in() && $id != '' && $id == get_staff_user_id()) {
            $select_str .= ',(SELECT COUNT(*) FROM tblnotifications WHERE touserid='.get_staff_user_id().' and isread=0) as total_unread_notifications, (SELECT COUNT(*) FROM tbltodoitems WHERE finished=0 AND staffid='.get_staff_user_id().') as total_unfinished_todos';
        }

        $this->db->select($select_str);

        if (is_int($active)) {
            $this->db->where('active', $active);
        }

        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('staffid', $id);
            $staff = $this->db->get('tblstaff')->row();
            if ($staff) {
                $staff->permissions = $this->get_staff_permissions($id);
            }

            return $staff;
        }
        $this->db->order_by('firstname', 'desc');

        return $this->db->get('tblstaff')->result_array();
    }

    public function get_staff_permissions($id)
    {
        $permissions = $this->object_cache->get('staff-'.$id.'-permissions');

        if(!$permissions && !is_array($permissions)){
            $this->db->select('tblstaffpermissions.*,tblpermissions.shortname as permission_name');
            $this->db->join('tblpermissions', 'tblpermissions.permissionid = tblstaffpermissions.permissionid');
            $this->db->where('staffid', $id);
            $permissions = $this->db->get('tblstaffpermissions')->result();
            $this->object_cache->add('staff-'.$id.'-permissions', $permissions);
        }
        return $permissions;
    }

    /**
     * Add new staff member
     * @param array $data staff $_POST data
     */
    public function add($data)
    {
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }
        // First check for all cases if the email exists.
        $this->db->where('email', $data['email']);
        $email = $this->db->get('tblstaff')->row();
        if ($email) {
            die('Email already exists');
        }
        $data['admin'] = 0;
        if (is_admin()) {
            if (isset($data['administrator'])) {
                $data['admin'] = 1;
                unset($data['administrator']);
            }
        }

        $send_welcome_email = true;
        $original_password  = $data['password'];
        if (!isset($data['send_welcome_email'])) {
            $send_welcome_email = false;
        } else {
            unset($data['send_welcome_email']);
        }
        $data['email_signature'] = nl2br_save_html($data['email_signature']);
        $this->load->helper('phpass');
        $hasher              = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        $data['password']    = $hasher->HashPassword($data['password']);
        $data['datecreated'] = date('Y-m-d H:i:s');
        if (isset($data['departments'])) {
            $departments = $data['departments'];
            unset($data['departments']);
        }

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

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if ($data['admin'] == 1) {
            $data['is_not_staff'] = 0;
        }

        $this->db->insert('tblstaff', $data);
        $staffid = $this->db->insert_id();
        if ($staffid) {
            $sl = $data['firstname'] . ' ' . $data['lastname'];
            if ($sl == ' ') {
                $sl = 'unknown-' . $staffid;
            }

            if ($send_welcome_email == true) {
                $this->load->model('emails_model');
                $merge_fields = array();
                $merge_fields = array_merge($merge_fields, get_staff_merge_fields($staffid, $original_password));
                $this->emails_model->send_email_template('new-staff-created', $data['email'], $merge_fields);
            }
            $this->db->where('staffid', $staffid);
            $this->db->update('tblstaff', array(
                'media_path_slug' => slug_it($sl),
            ));

            if (isset($custom_fields)) {
                handle_custom_fields_post($staffid, $custom_fields);
            }
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->insert('tblstaffdepartments', array(
                        'staffid' => $staffid,
                        'departmentid' => $department,
                    ));
                }
            }


            $_all_permissions = $this->roles_model->get_permissions();
            foreach ($_all_permissions as $permission) {
                $this->db->insert('tblstaffpermissions', array(
                    'permissionid' => $permission['permissionid'],
                    'staffid' => $staffid,
                    'can_view' => 0,
                    'can_view_own' => 0,
                    'can_edit' => 0,
                    'can_create' => 0,
                    'can_delete' => 0,
                ));
            }
            foreach ($this->perm_statements as $c) {
                foreach ($permissions as $key => $p) {
                    if ($key == $c) {
                        foreach ($p as $perm) {
                            $this->db->where('staffid', $staffid);
                            $this->db->where('permissionid', $perm);
                            $this->db->update('tblstaffpermissions', array(
                                'can_' . $c => 1,
                            ));
                        }
                    }
                }
            }

            logActivity('New Staff Member Added [ID: ' . $staffid . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']');
            // Delete all staff permission if is admin we dont need permissions stored in database (in case admin check some permissions)
            if ($data['admin'] == 1) {
                $this->db->where('staffid', $staffid);
                $this->db->delete('tblstaffpermissions');
            }
            // Get all announcements and set it to read.
            $this->db->select('announcementid');
            $this->db->from('tblannouncements');
            $this->db->where('showtostaff', 1);
            $announcements = $this->db->get()->result_array();
            foreach ($announcements as $announcement) {
                $this->db->insert('tbldismissedannouncements', array(
                    'announcementid' => $announcement['announcementid'],
                    'staff' => 1,
                    'userid' => $staffid,
                ));
            }
            do_action('staff_member_created', $staffid);

            return $staffid;
        }

        return false;
    }

    /**
     * Update staff member info
     * @param  array $data staff data
     * @param  mixed $id   staff id
     * @return boolean
     */
    public function update($data, $id)
    {
        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }
        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        $hook_data['data']   = $data;
        $hook_data['userid'] = $id;
        $hook_data           = do_action('before_update_staff_member', $hook_data);
        $data                = $hook_data['data'];
        $id                  = $hook_data['userid'];

        if (is_admin()) {
            if (isset($data['administrator'])) {
                $data['admin'] = 1;
                unset($data['administrator']);
            } else {
                if ($id != get_staff_user_id()) {
                    if ($id == 1) {
                        return array(
                            'cant_remove_main_admin' => true,
                        );
                    }
                } else {
                    return array(
                        'cant_remove_yourself_from_admin' => true,
                    );
                }
                $data['admin'] = 0;
            }
        }

        $affectedRows = 0;
        if (isset($data['departments'])) {
            $departments = $data['departments'];
            unset($data['departments']);
        }
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
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $this->load->helper('phpass');
            $hasher                       = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
            $data['password']             = $hasher->HashPassword($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }


        if (isset($data['two_factor_auth_enabled'])) {
            $data['two_factor_auth_enabled'] = 1;
        } else {
            $data['two_factor_auth_enabled'] = 0;
        }

        if (isset($data['is_not_staff'])) {
            $data['is_not_staff'] = 1;
        } else {
            $data['is_not_staff'] = 0;
        }

        if ($data['admin'] == 1) {
            $data['is_not_staff'] = 0;
        }

        $data['email_signature'] = nl2br_save_html($data['email_signature']);

        $this->load->model('departments_model');
        $staff_departments = $this->departments_model->get_staff_departments($id);
        if (sizeof($staff_departments) > 0) {
            if (!isset($data['departments'])) {
                $this->db->where('staffid', $id);
                $this->db->delete('tblstaffdepartments');
            } else {
                foreach ($staff_departments as $staff_department) {
                    if (isset($departments)) {
                        if (!in_array($staff_department['departmentid'], $departments)) {
                            $this->db->where('staffid', $id);
                            $this->db->where('departmentid', $staff_department['departmentid']);
                            $this->db->delete('tblstaffdepartments');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                    }
                }
            }
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->where('staffid', $id);
                    $this->db->where('departmentid', $department);
                    $_exists = $this->db->get('tblstaffdepartments')->row();
                    if (!$_exists) {
                        $this->db->insert('tblstaffdepartments', array(
                            'staffid' => $id,
                            'departmentid' => $department,
                        ));
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            }
        } else {
            if (isset($departments)) {
                foreach ($departments as $department) {
                    $this->db->insert('tblstaffdepartments', array(
                        'staffid' => $id,
                        'departmentid' => $department,
                    ));
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }


        $this->db->where('staffid', $id);
        $this->db->update('tblstaff', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($this->update_permissions($permissions, $id)) {
            $affectedRows++;
        }

        if (isset($data['admin']) && $data['admin'] == 1) {
            $this->db->where('staffid', $id);
            $this->db->delete('tblstaffpermissions');
        }
        if ($affectedRows > 0) {
            do_action('staff_member_updated', $id);
            logActivity('Staff Member Updated [ID: ' . $id . ', ' . $data['firstname'] . ' ' . $data['lastname'] . ']');

            return true;
        }

        return false;
    }

    public function update_permissions($permissions, $id)
    {
        $all_permissions = $this->roles_model->get_permissions();
        if (total_rows('tblstaffpermissions', array(
            'staffid' => $id,
        )) == 0) {
            foreach ($all_permissions as $p) {
                $_ins                 = array();
                $_ins['staffid']      = $id;
                $_ins['permissionid'] = $p['permissionid'];
                $this->db->insert('tblstaffpermissions', $_ins);
            }
        } elseif (total_rows('tblstaffpermissions', array(
                'staffid' => $id,
            )) != count($all_permissions)) {
            foreach ($all_permissions as $p) {
                if (total_rows('tblstaffpermissions', array(
                    'staffid' => $id,
                    'permissionid' => $p['permissionid'],
                )) == 0) {
                    $_ins                 = array();
                    $_ins['staffid']      = $id;
                    $_ins['permissionid'] = $p['permissionid'];
                    $this->db->insert('tblstaffpermissions', $_ins);
                }
            }
        }
        $_permission_restore_affected_rows = 0;
        foreach ($all_permissions as $permission) {
            foreach ($this->perm_statements as $c) {
                $this->db->where('staffid', $id);
                $this->db->where('permissionid', $permission['permissionid']);
                $this->db->update('tblstaffpermissions', array(
                    'can_' . $c => 0,
                ));
                if ($this->db->affected_rows() > 0) {
                    $_permission_restore_affected_rows++;
                }
            }
        }
        $_new_permissions_added_affected_rows = 0;
        foreach ($permissions as $key => $val) {
            foreach ($val as $p) {
                $this->db->where('staffid', $id);
                $this->db->where('permissionid', $p);
                $this->db->update('tblstaffpermissions', array(
                    'can_' . $key => 1,
                ));
                if ($this->db->affected_rows() > 0) {
                    $_new_permissions_added_affected_rows++;
                }
            }
        }
        if ($_new_permissions_added_affected_rows != $_permission_restore_affected_rows) {
            return true;
        }
    }

    public function update_profile($data, $id)
    {
        $hook_data['data']   = $data;
        $hook_data['userid'] = $id;
        $hook_data           = do_action('before_staff_update_profile', $hook_data);
        $data                = $hook_data['data'];
        $id                  = $hook_data['userid'];

        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $this->load->helper('phpass');
            $hasher                       = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
            $data['password']             = $hasher->HashPassword($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }

        if (isset($data['two_factor_auth_enabled'])) {
            $data['two_factor_auth_enabled'] = 1;
        } else {
            $data['two_factor_auth_enabled'] = 0;
        }

        $data['email_signature'] = nl2br_save_html($data['email_signature']);

        $this->db->where('staffid', $id);
        $this->db->update('tblstaff', $data);
        if ($this->db->affected_rows() > 0) {
            do_action('staff_member_profile_updated', $id);
            logActivity('Staff Profile Updated [Staff: ' . get_staff_full_name($id) . ']');

            return true;
        }

        return false;
    }

    /**
     * Change staff passwordn
     * @param  mixed $data   password data
     * @param  mixed $userid staff id
     * @return mixed
     */
    public function change_password($data, $userid)
    {
        $hook_data['data']   = $data;
        $hook_data['userid'] = $userid;
        $hook_data           = do_action('before_staff_change_password', $hook_data);
        $data                = $hook_data['data'];
        $userid              = $hook_data['userid'];

        $member = $this->get($userid);
        // CHeck if member is active
        if ($member->active == 0) {
            return array(
                array(
                    'memberinactive' => true,
                ),
            );
        }
        $this->load->helper('phpass');
        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        // Check new old password
        if (!$hasher->CheckPassword($data['oldpassword'], $member->password)) {
            return array(
                array(
                    'passwordnotmatch' => true,
                ),
            );
        }
        $data['newpasswordr'] = $hasher->HashPassword($data['newpasswordr']);
        $this->db->where('staffid', $userid);
        $this->db->update('tblstaff', array(
            'password' => $data['newpasswordr'],
            'last_password_change' => date('Y-m-d H:i:s'),
        ));
        if ($this->db->affected_rows() > 0) {
            logActivity('Staff Password Changed [' . $userid . ']');

            return true;
        }

        return false;
    }

    /**
     * Change staff status / active / inactive
     * @param  mixed $id     staff id
     * @param  mixed $status status(0/1)
     */
    public function change_staff_status($id, $status)
    {
        $hook_data['id']     = $id;
        $hook_data['status'] = $status;
        $hook_data           = do_action('before_staff_status_change', $hook_data);
        $status              = $hook_data['status'];
        $id                  = $hook_data['id'];

        $this->db->where('staffid', $id);
        $this->db->update('tblstaff', array(
            'active' => $status,
        ));
        logActivity('Staff Status Changed [StaffID: ' . $id . ' - Status(Active/Inactive): ' . $status . ']');
    }

    public function get_logged_time_data($id = '', $filter_data = array())
    {
        if ($id == '') {
            $id = get_staff_user_id();
        }
        $result['timesheets'] = array();
        $result['total']      = array();
        $result['this_month'] = array();

        $first_day_this_month = date('Y-m-01'); // hard-coded '01' for first day
        $last_day_this_month  = date('Y-m-t 23:59:59');

        $result['last_month'] = array();
        $first_day_last_month = date('Y-m-01', strtotime('-1 MONTH')); // hard-coded '01' for first day
        $last_day_last_month  = date('Y-m-t 23:59:59', strtotime('-1 MONTH'));

        $result['this_week'] = array();
        $first_day_this_week = date('Y-m-d', strtotime('monday this week'));
        $last_day_this_week  = date('Y-m-d 23:59:59', strtotime('sunday this week'));

        $result['last_week'] = array();

        $first_day_last_week = date('Y-m-d', strtotime('monday last week'));
        $last_day_last_week  = date('Y-m-d 23:59:59', strtotime('sunday last week'));

        $this->db->select('task_id,start_time,end_time,staff_id,tbltaskstimers.hourly_rate,name,tbltaskstimers.id,rel_id,rel_type');
        $this->db->where('staff_id', $id);
        $this->db->join('tblstafftasks', 'tblstafftasks.id = tbltaskstimers.task_id', 'left');
        $timers           = $this->db->get('tbltaskstimers')->result_array();
        $_end_time_static = time();

        $filter_period = false;
        if (isset($filter_data['period-from']) && $filter_data['period-from'] != '' && isset($filter_data['period-to']) && $filter_data['period-to'] != '') {
            $filter_period = true;
            $from          = to_sql_date($filter_data['period-from']);
            $from          = date('Y-m-d', strtotime($from));
            $to            = to_sql_date($filter_data['period-to']);
            $to            = date('Y-m-d', strtotime($to));
        }

        foreach ($timers as $timer) {
            $start_date = strftime('%Y-%m-%d', $timer['start_time']);

            $end_time = $timer['end_time'];

            if ($timer['end_time'] == null) {
                $end_time = $_end_time_static;
            }

            $total = $end_time - $timer['start_time'];

            $result['total'][] = $total;
            $timer['total']    = $total;
            $timer['end_time'] = $end_time;

            if ($start_date >= $first_day_this_month && $start_date <= $last_day_this_month) {
                $result['this_month'][] = $total;
                if (isset($filter_data['this_month']) && $filter_data['this_month'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
            if ($start_date >= $first_day_last_month && $start_date <= $last_day_last_month) {
                $result['last_month'][] = $total;
                if (isset($filter_data['last_month']) && $filter_data['last_month'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
            if ($start_date >= $first_day_this_week && $start_date <= $last_day_this_week) {
                $result['this_week'][] = $total;
                if (isset($filter_data['this_week']) && $filter_data['this_week'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
            if ($start_date >= $first_day_last_week && $start_date <= $last_day_last_week) {
                $result['last_week'][] = $total;
                if (isset($filter_data['last_week']) && $filter_data['last_week'] != '') {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }

            if ($filter_period == true) {
                if ($start_date >= $from && $start_date <= $to) {
                    $result['timesheets'][$timer['id']] = $timer;
                }
            }
        }
        $result['total']      = array_sum($result['total']);
        $result['this_month'] = array_sum($result['this_month']);
        $result['last_month'] = array_sum($result['last_month']);
        $result['this_week']  = array_sum($result['this_week']);
        $result['last_week']  = array_sum($result['last_week']);

        return $result;
    }
}
