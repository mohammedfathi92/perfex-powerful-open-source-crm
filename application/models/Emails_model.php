<?php
defined('BASEPATH') or exit('No direct script access allowed');
define('EMAIL_TEMPLATE_SEND', true);
class Emails_model extends CRM_Model
{
    private $attachment = array();
    private $client_email_templates;
    private $staff_email_templates;
    private $rel_id;
    private $rel_type;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('email');
        $this->client_email_templates = get_client_email_templates_slugs();
        $this->staff_email_templates  = get_staff_email_templates_slugs();
    }

    /**
     * @param  string
     * @return array
     * Get email template by type
     */
    public function get($where = array())
    {
        $this->db->where($where);

        return $this->db->get('tblemailtemplates')->result_array();
    }

    /**
     * @param  integer
     * @return object
     * Get email template by id
     */
    public function get_email_template_by_id($id)
    {
        $this->db->where('emailtemplateid', $id);

        return $this->db->get('tblemailtemplates')->row();
    }

    /**
     * Create new email template
     * @param mixed $data
     */
    public function add_template($data)
    {
        $this->db->insert('tblemailtemplates', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            return $insert_id;
        }

        return false;
    }

    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update email template
     */
    public function update($data)
    {
        if (isset($data['plaintext'])) {
            $data['plaintext'] = 1;
        } else {
            $data['plaintext'] = 0;
        }

        if (isset($data['disabled'])) {
            $data['active'] = 0;
            unset($data['disabled']);
        } else {
            $data['active'] = 1;
        }
        $main_id      = false;
        $affectedRows = 0;
        $i            = 0;
        foreach ($data['subject'] as $id => $val) {
            if ($i == 0) {
                $main_id = $id;
            }

            $_data              = array();
            $_data['subject']   = $val;
            $_data['fromname']  = $data['fromname'];
            $_data['fromemail'] = $data['fromemail'];
            $_data['message']   = $data['message'][$id];
            $_data['plaintext'] = $data['plaintext'];
            $_data['active']    = $data['active'];

            $this->db->where('emailtemplateid', $id);
            $this->db->update('tblemailtemplates', $_data);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }

            $i++;
        }
        $main_template = $this->get_email_template_by_id($main_id);

        if ($affectedRows > 0 && $main_template) {
            logActivity('Email Template Updated [' . $main_template->name . ']');

            return true;
        }

        return false;
    }

    /**
     * Change template to active/inactive
     * @param  string $slug    template slug
     * @param  mixed $enabled enabled or disabled / 1 or 0
     * @return boolean
     */
    public function mark_as($slug, $enabled)
    {
        $this->db->where('slug', $slug);
        $this->db->update('tblemailtemplates', array('active'=>$enabled));

        return $this->db->affected_rows() > 0 ? true : false;
    }

    /**
     * Change template to active/inactive
     * @param  string $type    template type
     * @param  mixed $enabled enabled or disabled / 1 or 0
     * @return boolean
     */
    public function mark_as_by_type($type,$enabled){
        $this->db->where('type', $type);
        $this->db->update('tblemailtemplates', array('active'=>$enabled));

        return $this->db->affected_rows() > 0 ? true : false;
    }

    /**
     * Send email - No templates used only simple string
     * @since Version 1.0.2
     * @param  string $email   email
     * @param  string $message message
     * @param  string $subject email subject
     * @return boolean
     */
    public function send_simple_email($email, $subject, $message)
    {
        $cnf = array(
            'from_email' => get_option('smtp_email'),
            'from_name' => get_option('companyname'),
            'email' => $email,
            'subject' => $subject,
            'message' => $message
        );

        // Simulate fake template to be parsed
        $template = new StdClass();
        $template->message = get_option('email_header').$cnf['message'].get_option('email_footer');
        $template->fromname = $cnf['from_name'];
        $template->subject =  $cnf['subject'];

        $template = parse_email_template($template);

        $cnf['message'] = $template->message;
        $cnf['from_name'] = $template->fromname;
        $cnf['subject'] = $template->subject;

        $cnf['message'] = check_for_links($cnf['message']);

        $cnf = do_action('before_send_simple_email', $cnf);

        if(isset($cnf['prevent_sending']) && $cnf['prevent_sending'] == true) {
            return false;
        }

        $this->email->clear(true);
        $this->email->set_newline("\r\n");
        $this->email->from($cnf['from_email'], $cnf['from_name']);
        $this->email->to($cnf['email']);

        $bcc = '';
        // Used for action hooks
        if (isset($cnf['bcc'])) {
            $bcc = $cnf['bcc'];
            if(is_array($bcc)) {
                $bcc = implode(', ',$bcc);
            }
        }

        $systemBCC = get_option('bcc_emails');
        if($systemBCC != ""){
            if($bcc != ""){
                $bcc .= ', '.$systemBCC;
            } else {
                $bcc .= $systemBCC;
            }
        }
        if($bcc != ''){
            $this->email->bcc($bcc);
        }

        if (isset($cnf['cc'])) {
            $this->email->cc($cnf['cc']);
        }

        if (isset($cnf['reply_to'])) {
            $this->email->reply_to($cnf['reply_to']);
        }

        $this->email->subject($cnf['subject']);
        $this->email->message($cnf['message']);

        $alt_message = strip_html_tags($cnf['message']);
        $this->email->set_alt_message(trim($alt_message));

        if (count($this->attachment) > 0) {
            foreach ($this->attachment as $attach) {
                if (!isset($attach['read'])) {
                    $this->email->attach($attach['attachment'], 'attachment', $attach['filename'], $attach['type']);
                } else {
                    if (!isset($attach['filename']) || (isset($attach['filename']) && empty($attach['filename']))) {
                        $attach['filename'] = basename($attach['attachment']);
                    }
                    $this->email->attach($attach['attachment'], '', $attach['filename']);
                }
            }
        }

        $this->clear_attachments();
        if ($this->email->send()) {
            logActivity('Email sent to: ' . $cnf['email'] . ' Subject: ' . $cnf['subject']);

            return true;
        }

        return false;
    }

    /**
     * Send email template
     * @param  string $template_slug email template slug
     * @param  string $email         email to send
     * @param  array $merge_fields  merge field
     * @param  string $ticketid      used only when sending email templates linked to ticket / used for piping
     * @param  mixed $cc
     * @return boolean
     */
    public function send_email_template($template_slug, $email, $merge_fields, $ticketid = '', $cc = '')
    {
        $template                     = get_email_template_for_sending($template_slug, $email);
        $staff_email_templates_slugs  = get_staff_email_templates_slugs();
        $client_email_templates_slugs = get_client_email_templates_slugs();

        $inactive_user_table_check = '';

        /**
         * Dont send email templates for non active contacts/staff
         * Do checking here
         */
        if (in_array($template_slug, $staff_email_templates_slugs)) {
            $inactive_user_table_check = 'tblstaff';
        } elseif (in_array($template_slug, $client_email_templates_slugs)) {
            $inactive_user_table_check = 'tblcontacts';
        }

        /**
         * Is really inactive?
         */
        if ($inactive_user_table_check != '') {
            $this->db->select('active')->where('email', $email);
            $user = $this->db->get($inactive_user_table_check)->row();
            if ($user && $user->active == 0) {
                return false;
            }
        }

        /**
         * Template not found?
         */
        if (!$template) {
            logActivity('Failed to send email template [Template not found]');

            return false;
        }

        /**
         * Template is disabled or invalid email?
         * Log activity
         */
        if ($template->active == 0 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->clear_attachments();

            $this->db->where('language', 'english');
            $this->db->where('slug', $template->slug);
            $tmpTemplate = $this->db->get('tblemailtemplates')->row();

            if ($tmpTemplate) {
                logActivity('Failed to send email template [<a href="'.admin_url('emails/email_template/'.$tmpTemplate->emailtemplateid).'">'.$template->name.'</a>] [Reason: Email template is disabled.]');
            }

            return false;
        }


        $template = parse_email_template($template, $merge_fields);
        $template->message = get_option('email_header') . $template->message . get_option('email_footer');

        // Parse merge fields again in case there is merge fields found in email_header and email_footer option.
        // We cant parse this in parse_email_template function because in case the template content is send via $_POST wont work
        $template = _parse_email_template_merge_fields($template, $merge_fields);

        /**
         * Template is plain text?
         */
        if ($template->plaintext == 1) {
            $this->config->set_item('mailtype', 'text');
            $template->message = strip_html_tags($template->message, '<br/>, <br>');
            // Replace <br /> with \n
            $template->message = clear_textarea_breaks($template->message);
            $template->message = trim($template->message);
        }

        $fromemail = $template->fromemail;
        $fromname  = $template->fromname;

        if ($fromemail == '') {
            $fromemail = get_option('smtp_email');
        }
        if ($fromname == '') {
            $fromname = get_option('companyname');
        }

        /**
         * Ticket variables
        */
        $reply_to = false;
        $from_header_dept_email = false;
        /**
         * Tickets template
         * For tickets there is different config
         */
        if (is_numeric($ticketid) && $template->type == 'ticket') {

            if(!class_exists('tickets_model')){
                $this->load->model('tickets_model');
            }

            $this->db->select('tbldepartments.email as department_email, email_from_header as dept_email_from_header')
            ->where('ticketid',$ticketid)
            ->join('tbldepartments','tbldepartments.departmentid=tbltickets.department','left');

            $ticket = $this->db->get('tbltickets')->row();

            if (!empty($ticket->department_email) && filter_var($ticket->department_email, FILTER_VALIDATE_EMAIL)) {
                $reply_to = $ticket->department_email;
                $from_header_dept_email = $ticket->dept_email_from_header == 1;
            }
            /**
             * IMPORTANT
             * Do not change/remove this line, this is used for email piping so the software can recognize the ticket id.
             */
            if (substr($template->subject, 0, 10) != "[Ticket ID") {
                $template->subject = '[Ticket ID: ' . $ticketid . '] ' . $template->subject;
            }
        }

        $hook_data['template'] = $template;
        $hook_data['email']    = $email;
        $hook_data['attachments'] = $this->attachment;

        $hook_data['template']->message = check_for_links($hook_data['template']->message);

        $hook_data = do_action('before_email_template_send', $hook_data);

        $template = $hook_data['template'];
        $email    = $hook_data['email'];
        $attachments    = $hook_data['attachments'];

        if (isset($template->prevent_sending) && $template->prevent_sending == true) {
            $this->clear_attachments();
            return false;
        }

        $this->email->clear(true);
        $this->email->set_newline("\r\n");
        $this->email->from(($from_header_dept_email ? $ticket->department_email : $fromemail), $fromname);
        $this->email->subject($template->subject);

        $this->email->message($template->message);

        if (is_array($cc) || !empty($cc)) {
            $this->email->cc($cc);
        }

        $bcc = '';
        // Used for action hooks
        if (isset($template->bcc)) {
            $bcc = $template->bcc;
            if(is_array($bcc)) {
                $bcc = implode(', ',$bcc);
            }
        }

        $systemBCC = get_option('bcc_emails');
        if($systemBCC != ""){
            if($bcc != ""){
                $bcc .= ', '.$systemBCC;
            } else {
                $bcc .= $systemBCC;
            }
        }

        if($bcc != ''){
            $bcc = array_map('trim',explode(',',$bcc));
            $bcc = array_unique($bcc);
            $bcc = implode(', ', $bcc);
            $this->email->bcc($bcc);
        }

        if ($reply_to != false) {
            $this->email->reply_to($reply_to);
        } elseif (isset($template->reply_to)) {
            $this->email->reply_to($template->reply_to);
        }

        if ($template->plaintext == 0) {
            $alt_message = strip_html_tags($template->message, '<br/>, <br>');
            // Replace <br /> with \n
            $alt_message = clear_textarea_breaks($alt_message);
            $this->email->set_alt_message(trim($alt_message));
        }

        $this->email->to($email);

        if (count($attachments) > 0) {
            foreach ($attachments as $attach) {
                if (!isset($attach['read'])) {
                    $this->email->attach($attach['attachment'], 'attachment', $attach['filename'], $attach['type']);
                } else {
                    $this->email->attach($attach['attachment'], '', $attach['filename']);
                }
            }
        }

        $this->clear_attachments();

        if ($this->email->send()) {
            logActivity('Email Send To [Email: ' . $email . ', Template: ' . $template->name . ']');

            return true;
        } else {
            if (ENVIRONMENT !== 'production') {
                logActivity('Failed to send email template - ' . $this->email->print_debugger());
            }
        }

        return false;
    }

    /**
     * @param resource
     * @param string
     * @param string (mime type)
     * @return none
     * Add attachment to property to check before an email is send
     */
    public function add_attachment($attachment)
    {
        $this->attachment[] = $attachment;
    }

    /**
     * @return none
     * Clear all attachment properties
     */
    private function clear_attachments()
    {
        $this->attachment = array();
    }

    public function set_rel_id($rel_id)
    {
        $this->rel_id = $rel_id;
    }

    public function set_rel_type($rel_type)
    {
        $this->rel_type = $rel_type;
    }

    public function get_rel_id()
    {
        return $this->rel_id;
    }

    public function get_rel_type()
    {
        return $this->rel_type;
    }
}
