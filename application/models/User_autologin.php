<?php
defined('BASEPATH') or exit('No direct script access allowed');
class User_Autologin extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Check if autologin found
     * @param  mixed $user_id clientid/staffid
     * @param  string $key     key from cookie to retrieve from database
     * @return mixed
     */
    public function get($user_id, $key)
    {
        // check if user is staff
        $this->db->where('user_id', $user_id);
        $this->db->where('key_id', $key);
        $user = $this->db->get('tbluserautologin')->row();
        if (!$user) {
            return null;
        }
        if ($user->staff == 1) {
            $table = 'tblstaff';
            $this->db->select($table . '.staffid as id');
            $_id   = 'staffid';
            $staff = true;
        } else {
            $table = 'tblcontacts';
            $this->db->select($table . '.id as id');
            $_id   = 'id';
            $staff = false;
        }
        $this->db->select($table . '.' . $_id);
        $this->db->from($table);
        $this->db->join('tbluserautologin', 'tbluserautologin.user_id = ' . $table . '.' . $_id);
        $this->db->where('tbluserautologin.user_id', $user_id);
        $this->db->where('tbluserautologin.key_id', $key);
        $query = $this->db->get();
        if ($query) {
            if ($query->num_rows() == 1) {
                $user        = $query->row();
                $user->staff = $staff;

                return $user;
            }
        }

        return null;
    }

    /**
     * Set new autologin if user have clicked remember me
     * @param mixed $user_id clientid/userid
     * @param string $key     cookie key
     * @param integer $staff   is staff or client
     */
    public function set($user_id, $key, $staff)
    {
        return $this->db->insert('tbluserautologin', array(
            'user_id' => $user_id,
            'key_id' => $key,
            'user_agent' => substr($this->input->user_agent(), 0, 149),
            'last_ip' => $this->input->ip_address(),
            'staff' => $staff
        ));
    }

    /**
     * Delete user autologin
     * @param  mixed $user_id clientid/userid
     * @param  string $key     cookie key
     * @param integer $staff   is staff or client
     */
    public function delete($user_id, $key, $staff)
    {
        $this->db->where('user_id', $user_id);
        $this->db->where('key_id', $key);
        $this->db->where('staff', $staff);
        $this->db->delete('tbluserautologin');
    }
}
