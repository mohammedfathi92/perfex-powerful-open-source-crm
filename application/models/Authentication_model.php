<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Authentication_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('user_autologin');
        $this->autologin();
    }

    /**
     * @param  string Email address for login
     * @param  string User Password
     * @param  boolean Set cookies for user if remember me is checked
     * @param  boolean Is Staff Or Client
     * @return boolean if not redirect url found, if found redirect to the url
     */
    public function login($email, $password, $remember, $staff)
    {
        if ((!empty($email)) and (!empty($password))) {
            $table = 'tblcontacts';
            $_id   = 'id';
            if ($staff == true) {
                $table = 'tblstaff';
                $_id   = 'staffid';
            }
            $this->db->where('email', $email);
            $user = $this->db->get($table)->row();
            if ($user) {
                // Email is okey lets check the password now
                $this->load->helper('phpass');
                $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
                if (!$hasher->CheckPassword($password, $user->password)) {
                    // Password failed, return
                    return false;
                }
            } else {
                logActivity('Failed Login Attempt [Email:' . $email . ', Is Staff Member:' . ($staff == true ? 'Yes' : 'No') . ', IP:' . $this->input->ip_address() . ']');

                return false;
            }

            if ($user->active == 0) {
                logActivity('Inactive User Tried to Login [Email:' . $email . ', Is Staff Member:' . ($staff == true ? 'Yes' : 'No') . ', IP:' . $this->input->ip_address() . ']');

                return array(
                    'memberinactive' => true,
                );
            }

            $twoFactorAuth = false;
            if ($staff == true) {
                $twoFactorAuth = $user->two_factor_auth_enabled == 0 ? false : true;

                if (!$twoFactorAuth) {
                    do_action('before_staff_login', array(
                        'email' => $email,
                        'userid' => $user->$_id,
                    ));
                    $user_data = array(
                        'staff_user_id' => $user->$_id,
                        'staff_logged_in' => true,
                    );
                } else {
                    $user_data = array();
                    if ($remember) {
                        $user_data['tfa_remember'] = true;
                    }
                }
            } else {
                do_action('before_client_login', array(
                    'email' => $email,
                    'userid' => $user->userid,
                    'contact_user_id' => $user->$_id,
                ));

                $user_data = array(
                    'client_user_id' => $user->userid,
                    'contact_user_id' => $user->$_id,
                    'client_logged_in' => true,
                );
            }

            $this->session->set_userdata($user_data);

            if (!$twoFactorAuth) {
                if ($remember) {
                    $this->create_autologin($user->$_id, $staff);
                }

                $this->update_login_info($user->$_id, $staff);
            } else {
                return array('two_factor_auth'=>true, 'user'=>$user);
            }

            return true;
        }

        return false;
    }

    /**
     * @param  boolean If Client or Staff
     * @return none
     */
    public function logout($staff = true)
    {
        $this->delete_autologin($staff);
        if (is_client_logged_in()) {
            do_action('before_client_logout', get_client_user_id());
            $this->session->unset_userdata('client_user_id');
            $this->session->unset_userdata('client_logged_in');
        } else {
            do_action('before_staff_logout', get_client_user_id());
            $this->session->unset_userdata('staff_user_id');
            $this->session->unset_userdata('staff_logged_in');
        }
        $this->session->sess_destroy();
    }

    /**
     * @param  integer ID to create autologin
     * @param  boolean Is Client or Staff
     * @return boolean
     */
    private function create_autologin($user_id, $staff)
    {
        $this->load->helper('cookie');
        $key = substr(md5(uniqid(rand() . get_cookie($this->config->item('sess_cookie_name')))), 0, 16);
        $this->user_autologin->delete($user_id, $key, $staff);
        if ($this->user_autologin->set($user_id, md5($key), $staff)) {
            set_cookie(array(
                'name' => 'autologin',
                'value' => serialize(array(
                    'user_id' => $user_id,
                    'key' => $key,
                )),
                'expire' => 60 * 60 * 24 * 31 * 2, // 2 months
            ));

            return true;
        }

        return false;
    }

    /**
     * @param  boolean Is Client or Staff
     * @return none
     */
    private function delete_autologin($staff)
    {
        $this->load->helper('cookie');
        if ($cookie = get_cookie('autologin', true)) {
            $data = unserialize($cookie);
            $this->user_autologin->delete($data['user_id'], md5($data['key']), $staff);
            delete_cookie('autologin', 'aal');
        }
    }

    /**
     * @return boolean
     * Check if autologin found
     */
    public function autologin()
    {
        if (!is_logged_in()) {
            $this->load->helper('cookie');
            if ($cookie = get_cookie('autologin', true)) {
                $data = unserialize($cookie);
                if (isset($data['key']) and isset($data['user_id'])) {
                    if (!is_null($user = $this->user_autologin->get($data['user_id'], md5($data['key'])))) {
                        // Login user
                        if ($user->staff == 1) {
                            $user_data = array(
                                'staff_user_id' => $user->id,
                                'staff_logged_in' => true,
                            );
                        } else {
                            // Get the customer id
                            $this->db->select('userid');
                            $this->db->where('id', $user->id);
                            $contact = $this->db->get('tblcontacts')->row();

                            $user_data = array(
                                'client_user_id' => $contact->userid,
                                'contact_user_id' => $user->id,
                                'client_logged_in' => true,
                            );
                        }
                        $this->session->set_userdata($user_data);
                        // Renew users cookie to prevent it from expiring
                        set_cookie(array(
                            'name' => 'autologin',
                            'value' => $cookie,
                            'expire' => 60 * 60 * 24 * 31 * 2, // 2 months
                        ));
                        $this->update_login_info($user->id, $user->staff);

                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param  integer ID
     * @param  boolean Is Client or Staff
     * @return none
     * Update login info on autologin
     */
    private function update_login_info($user_id, $staff)
    {
        $table = 'tblcontacts';
        $_id   = 'id';
        if ($staff == true) {
            $table = 'tblstaff';
            $_id   = 'staffid';
        }
        $this->db->set('last_ip', $this->input->ip_address());
        $this->db->set('last_login', date('Y-m-d H:i:s'));
        $this->db->where($_id, $user_id);
        $this->db->update($table);
    }

    /**
     * Send set password email
     * @param string $email
     * @param boolean $staff is staff of contact
     */
    public function set_password_email($email, $staff)
    {
        $table = 'tblcontacts';
        $_id   = 'id';
        if ($staff == true) {
            $table = 'tblstaff';
            $_id   = 'staffid';
        }
        $this->db->where('email', $email);
        $user = $this->db->get($table)->row();
        if ($user) {
            if ($user->active == 0) {
                return array(
                    'memberinactive' => true,
                );
            }

            $new_pass_key = app_generate_hash();
            $this->db->where($_id, $user->$_id);
            $this->db->update($table, array(
                'new_pass_key' => $new_pass_key,
                'new_pass_key_requested' => date('Y-m-d H:i:s'),
            ));
            if ($this->db->affected_rows() > 0) {
                $this->load->model('emails_model');
                $data['new_pass_key'] = $new_pass_key;
                $data['staff']        = $staff;
                $data['userid']       = $user->$_id;
                $data['email']        = $email;

                $merge_fields = array();
                if ($staff == false) {
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($user->userid, $user->$_id));
                } else {
                    $merge_fields = array_merge($merge_fields, get_staff_merge_fields($user->$_id));
                }
                $merge_fields = array_merge($merge_fields, get_password_merge_field($data, $staff, 'set'));
                $send         = $this->emails_model->send_email_template('contact-set-password', $user->email, $merge_fields);

                if ($send) {
                    return true;
                }

                return false;
            }

            return false;
        }

        return false;
    }

    /**
     * @param  string Email from the user
     * @param  Is Client or Staff
     * @return boolean
     * Generate new password key for the user to reset the password.
     */
    public function forgot_password($email, $staff = false)
    {
        $table = 'tblcontacts';
        $_id   = 'id';
        if ($staff == true) {
            $table = 'tblstaff';
            $_id   = 'staffid';
        }
        $this->db->where('email', $email);
        $user = $this->db->get($table)->row();

        if ($user) {
            if ($user->active == 0) {
                return array(
                    'memberinactive' => true,
                );
            }

            $new_pass_key = app_generate_hash();
            $this->db->where($_id, $user->$_id);
            $this->db->update($table, array(
                'new_pass_key' => $new_pass_key,
                'new_pass_key_requested' => date('Y-m-d H:i:s'),
            ));

            if ($this->db->affected_rows() > 0) {
                $this->load->model('emails_model');
                $data['new_pass_key'] = $new_pass_key;
                $data['staff']        = $staff;
                $data['userid']       = $user->$_id;
                $merge_fields = array();
                if ($staff == false) {
                    $template     = 'contact-forgot-password';
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($user->userid, $user->$_id));
                } else {
                    $template     = 'staff-forgot-password';
                    $merge_fields = array_merge($merge_fields, get_staff_merge_fields($user->$_id));
                }
                $merge_fields = array_merge($merge_fields, get_password_merge_field($data, $staff, 'forgot'));
                $send         = $this->emails_model->send_email_template($template, $user->email, $merge_fields);
                if ($send) {
                    return true;
                }

                return false;
            }

            return false;
        }

        return false;
    }

    /**
     * Update user password from forgot password feature or set password
     * @param boolean $staff        is staff or contact
     * @param mixed $userid
     * @param string $new_pass_key the password generate key
     * @param string $password     new password
     */
    public function set_password($staff, $userid, $new_pass_key, $password)
    {
        if (!$this->can_set_password($staff, $userid, $new_pass_key)) {
            return array(
                'expired' => true,
            );
        }
        $this->load->helper('phpass');
        $hasher   = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        $password = $hasher->HashPassword($password);
        $table    = 'tblcontacts';
        $_id      = 'id';
        if ($staff == true) {
            $table = 'tblstaff';
            $_id   = 'staffid';
        }
        $this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $this->db->update($table, array(
            'password' => $password,
        ));
        if ($this->db->affected_rows() > 0) {
            logActivity('User Set Password [User ID:' . $userid . ', Is Staff Member:' . ($staff == true ? 'Yes' : 'No') . ', IP:' . $this->input->ip_address() . ']');
            $this->db->set('new_pass_key', null);
            $this->db->set('new_pass_key_requested', null);
            $this->db->set('last_password_change', date('Y-m-d H:i:s'));
            $this->db->where($_id, $userid);
            $this->db->where('new_pass_key', $new_pass_key);
            $this->db->update($table);

            return true;
        }

        return null;
    }

    /**
     * @param  boolean Is Client or Staff
     * @param  integer ID
     * @param  string
     * @param  string
     * @return boolean
     * User reset password after successful validation of the key
     */
    public function reset_password($staff, $userid, $new_pass_key, $password)
    {
        if (!$this->can_reset_password($staff, $userid, $new_pass_key)) {
            return array(
                'expired' => true,
            );
        }
        $this->load->helper('phpass');
        $hasher   = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        $password = $hasher->HashPassword($password);
        $table    = 'tblcontacts';
        $_id      = 'id';
        if ($staff == true) {
            $table = 'tblstaff';
            $_id   = 'staffid';
        }

        $this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $this->db->update($table, array(
            'password' => $password,
        ));
        if ($this->db->affected_rows() > 0) {
            logActivity('User Reseted Password [User ID:' . $userid . ', Is Staff Member:' . ($staff == true ? 'Yes' : 'No') . ', IP:' . $this->input->ip_address() . ']');
            $this->db->set('new_pass_key', null);
            $this->db->set('new_pass_key_requested', null);
            $this->db->set('last_password_change', date('Y-m-d H:i:s'));
            $this->db->where($_id, $userid);
            $this->db->where('new_pass_key', $new_pass_key);
            $this->db->update($table);
            $this->load->model('emails_model');
            $this->db->where($_id, $userid);
            $user          = $this->db->get($table)->row();
            $data['email'] = $user->email;

            $merge_fields = array();
            if ($staff == false) {
                $template     = 'contact-password-reseted';
                $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($user->userid, $user->$_id));
            } else {
                $template     = 'staff-password-reseted';
                $merge_fields = array_merge($merge_fields, get_staff_merge_fields($user->$_id));
            }
            $this->emails_model->send_email_template($template, $user->email, $merge_fields);

            return true;
        }

        return null;
    }

    /**
     * @param  integer Is Client or Staff
     * @param  integer ID
     * @param  string Password reset key
     * @return boolean
     * Check if the key is not expired or not exists in database
     */
    public function can_reset_password($staff, $userid, $new_pass_key)
    {
        $table = 'tblcontacts';
        $_id   = 'id';
        if ($staff == true) {
            $table = 'tblstaff';
            $_id   = 'staffid';
        }

        $this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $user = $this->db->get($table)->row();
        if ($user) {
            $timestamp_now_minus_1_hour = time() - (60 * 60);
            $new_pass_key_requested     = strtotime($user->new_pass_key_requested);
            if ($timestamp_now_minus_1_hour > $new_pass_key_requested) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * @param  integer Is Client or Staff
     * @param  integer ID
     * @param  string Password reset key
     * @return boolean
     * Check if the key is not expired or not exists in database
     */
    public function can_set_password($staff, $userid, $new_pass_key)
    {
        $table = 'tblcontacts';
        $_id   = 'id';
        if ($staff == true) {
            $table = 'tblstaff';
            $_id   = 'staffid';
        }
        $this->db->where($_id, $userid);
        $this->db->where('new_pass_key', $new_pass_key);
        $user = $this->db->get($table)->row();
        if ($user) {
            $timestamp_now_minus_48_hour = time() - (3600 * 48);
            $new_pass_key_requested      = strtotime($user->new_pass_key_requested);
            if ($timestamp_now_minus_48_hour > $new_pass_key_requested) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get user from database by 2 factor authentication code
     * @param  string $code authentication code to search for
     * @return object
     */
    public function get_user_by_two_factor_auth_code($code)
    {
        $this->db->where('two_factor_auth_code', $code);

        return $this->db->get('tblstaff')->row();
    }

    /**
     * Login user via two factor authentication
     * @param  object $user user object
     * @return boolean
     */
    public function two_factor_auth_login($user)
    {
        do_action(
            'before_staff_login',
            array(
                'email' => $user->email,
                'userid' => $user->staffid,
                )
        );

        $this->session->set_userdata(
            array(
                'staff_user_id' => $user->staffid,
                'staff_logged_in' => true,
            )
        );

        $remember = null;
        if ($this->session->has_userdata('tfa_remember')) {
            $remember = true;
            $this->session->unset_userdata('tfa_remember');
        }

        if ($remember) {
            $this->create_autologin($user->staffid, true);
        }

        $this->update_login_info($user->staffid, true);

        return true;
    }

    /**
     * Check if 2 factor authentication code is valid for usage
     * @param  string  $code auth code
     * @return boolean
     */
    public function is_two_factor_code_valid($code)
    {
        $this->db->select('two_factor_auth_code_requested');
        $this->db->where('two_factor_auth_code', $code);
        $user = $this->db->get('tblstaff')->row();

        // Code not exists because no user is found
        if (!$user) {
            return false;
        }

        $timestamp_minus_1_hour = time() - (60 * 60);
        $new_code_key_requested     = strtotime($user->two_factor_auth_code_requested);
        // The code is older then 1 hour and its not valid
        if ($timestamp_minus_1_hour > $new_code_key_requested) {
            return false;
        }
        // Code is valid
        return true;
    }

    /**
     * Clears 2 factor authentication code in database
     * @param  mixed $id
     * @return boolean
     */
    public function clear_two_factor_auth_code($id)
    {
        $this->db->where('staffid', $id);
        $this->db->update('tblstaff', array(
            'two_factor_auth_code'=>null,
        ));

        return true;
    }

    /**
     * Set 2 factor authentication code for staff member
     * @param mixed $id staff id
     */
    public function set_two_factor_auth_code($id)
    {
        $code = generate_two_factor_auth_key();
        $code .= $id;

        $this->db->where('staffid', $id);
        $this->db->update('tblstaff', array(
            'two_factor_auth_code'=>$code,
            'two_factor_auth_code_requested'=>date('Y-m-d H:i:s'),
        ));

        return $code;
    }
}
