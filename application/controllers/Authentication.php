<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Authentication extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->misc_model->is_db_upgrade_required()) {
            redirect(admin_url());
        }
        load_admin_language();
        $this->load->model('Authentication_model');
        $this->load->library('form_validation');

        $this->form_validation->set_message('required', _l('form_validation_required'));
        $this->form_validation->set_message('valid_email', _l('form_validation_valid_email'));
        $this->form_validation->set_message('matches', _l('form_validation_matches'));
    }

    public function index()
    {
        $this->admin();
    }

    public function admin()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }

        $this->form_validation->set_rules('password', _l('admin_auth_login_password'), 'required');
        $this->form_validation->set_rules('email', _l('admin_auth_login_email'), 'trim|required|valid_email');
        if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '') {
            $this->form_validation->set_rules('g-recaptcha-response', 'Captcha', 'callback_recaptcha');
        }
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {

                $email = $this->input->post('email');
                $password = $this->input->post('password',false);
                $remember = $this->input->post('remember');

                $data = $this->Authentication_model->login($email, $password, $remember, true);

                if (is_array($data) && isset($data['memberinactive'])) {
                    set_alert('danger', _l('admin_auth_inactive_account'));
                    redirect(site_url('authentication/admin'));
                } elseif(is_array($data) && isset($data['two_factor_auth'])){

                    $this->Authentication_model->set_two_factor_auth_code($data['user']->staffid);

                    $this->load->model('emails_model');

                    $sent = $this->emails_model->send_email_template(
                        'two-factor-authentication',
                        $email,
                        get_staff_merge_fields($data['user']->staffid));

                    if(!$sent){
                        set_alert('danger',_l('two_factor_auth_failed_to_send_code'));
                        redirect(site_url('authentication/admin'));
                    } else {
                        set_alert('success',_l('two_factor_auth_code_sent_successfully',$email));
                    }
                    redirect(site_url('authentication/two_factor'));

                } elseif ($data == false) {
                    set_alert('danger', _l('admin_auth_invalid_email_or_password'));
                    redirect(site_url('authentication/admin'));
                } else {
                    // is logged in
                    $this->_url_redirect_after_login();
                }
                do_action('after_staff_login');
                redirect(admin_url());
            }
        }

        $data['title'] = _l('admin_auth_login_heading');
        $this->load->view('authentication/login_admin', $data);
    }

    public function two_factor(){

        $this->form_validation->set_rules('code', _l('two_factor_authentication_code'), 'required');

        if($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $code = $this->input->post('code');
                $code = trim($code);
                if($this->Authentication_model->is_two_factor_code_valid($code)){
                    $user = $this->Authentication_model->get_user_by_two_factor_auth_code($code);
                    $this->Authentication_model->clear_two_factor_auth_code($user->staffid);
                    $this->Authentication_model->two_factor_auth_login($user);
                    $this->_url_redirect_after_login();
                    do_action('after_staff_login');
                    redirect(admin_url());
                } else {
                    set_alert('danger', _l('two_factor_code_not_valid'));
                    redirect(site_url('authentication/two_factor'));
                }
            }
        }
        $this->load->view('authentication/set_two_factor_auth_code');
    }

    public function recaptcha($str = '')
    {
        return do_recaptcha_validation($str);
    }

    public function forgot_password()
    {
        if (is_staff_logged_in()) {
            redirect(admin_url());
        }
        $this->form_validation->set_rules('email', _l('admin_auth_login_email'), 'trim|required|valid_email|callback_email_exists');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $success = $this->Authentication_model->forgot_password($this->input->post('email'), true);
                if (is_array($success) && isset($success['memberinactive'])) {
                    set_alert('danger', _l('inactive_account'));
                    redirect(site_url('authentication/forgot_password'));
                } elseif ($success == true) {
                    set_alert('success', _l('check_email_for_resetting_password'));
                    redirect(site_url('authentication/admin'));
                } else {
                    set_alert('danger', _l('error_setting_new_password_key'));
                    redirect(site_url('authentication/forgot_password'));
                }
            }
        }
        $this->load->view('authentication/forgot_password');
    }

    public function reset_password($staff, $userid, $new_pass_key)
    {
        if (!$this->Authentication_model->can_reset_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(site_url('authentication/admin'));
        }
        $this->form_validation->set_rules('password', _l('admin_auth_reset_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('admin_auth_reset_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                do_action('before_user_reset_password', array(
                    'staff' => $staff,
                    'userid' => $userid
                ));
                $success = $this->Authentication_model->reset_password($staff, $userid, $new_pass_key, $this->input->post('passwordr',false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    do_action('after_user_reset_password', array(
                        'staff' => $staff,
                        'userid' => $userid
                    ));
                    set_alert('success', _l('password_reset_message'));
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                redirect(site_url('authentication/admin'));
            }
        }
        $this->load->view('authentication/reset_password');
    }

    public function set_password($staff, $userid, $new_pass_key)
    {
        if (!$this->Authentication_model->can_set_password($staff, $userid, $new_pass_key)) {
            set_alert('danger', _l('password_reset_key_expired'));
            redirect(site_url('authentication/admin'));
            if ($staff == 1) {
                redirect(site_url('authentication/admin'));
            } else {
                redirect(site_url());
            }
        }
        $this->form_validation->set_rules('password', _l('admin_auth_set_password'), 'required');
        $this->form_validation->set_rules('passwordr', _l('admin_auth_set_password_repeat'), 'required|matches[password]');
        if ($this->input->post()) {
            if ($this->form_validation->run() !== false) {
                $success = $this->Authentication_model->set_password($staff, $userid, $new_pass_key, $this->input->post('passwordr',false));
                if (is_array($success) && $success['expired'] == true) {
                    set_alert('danger', _l('password_reset_key_expired'));
                } elseif ($success == true) {
                    set_alert('success', _l('password_reset_message'));
                } else {
                    set_alert('danger', _l('password_reset_message_fail'));
                }
                if ($staff == 1) {
                    redirect(site_url('authentication/admin'));
                } else {
                    redirect(site_url());
                }
            }
        }
        $this->load->view('authentication/set_password');
    }



    public function logout()
    {
        $this->Authentication_model->logout();
        do_action('after_user_logout');
        redirect(site_url('authentication/admin'));
    }

    public function email_exists($email)
    {
        $total_rows = total_rows('tblstaff', array(
            'email' => $email
        ));
        if ($total_rows == 0) {
            $this->form_validation->set_message('email_exists', _l('auth_reset_pass_email_not_found'));

            return false;
        }

        return true;
    }
    /**
     * Check if user accessed url while not logged in to redirect after login
     * @return null
     */
     private function _url_redirect_after_login(){
            // This is only working for staff members
        if ($this->session->has_userdata('red_url')) {
            $red_url = $this->session->userdata('red_url');
            $this->session->unset_userdata('red_url');
            redirect($red_url);
        }
    }
}
