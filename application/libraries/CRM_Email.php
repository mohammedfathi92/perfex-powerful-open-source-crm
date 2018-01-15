<?php defined('BASEPATH') or exit('No direct script access allowed');

class CRM_Email extends CI_Email
{
    // Email Queue Table
    private $email_queue_table = 'tblemailqueue';

    // Status (pending, sending, sent, failed)
    private $status;

    /**
     * Constructor
     */
    public function __construct($config = array())
    {
        parent::__construct($config);
        log_message('debug', 'Email Queue Class Initialized');
        $this->CI = & get_instance();
    }

    public function set_status($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get
     *
     * Get queue emails.
     * @return  mixed
     */
    public function get_queue_emails($limit = null, $offset = null)
    {
        if ($this->status != false) {
            $this->CI->db->where('q.status', $this->status);
        }
        $query = $this->CI->db->get("{$this->email_queue_table} q", $limit, $offset);

        return $query->result();
    }

    /**
     * Save
     *
     * Add queue email to database.
     * @return  mixed
     */
    public function send($skip_job = false)
    {
        $attachments = $this->_attachments;

        $emailQueue = get_option('email_queue_enabled');
        $queueSkipAttachment = get_option('email_queue_skip_with_attachments');

        if ($skip_job === true
            || $emailQueue == '0'
            || ($emailQueue == '1' && $queueSkipAttachment == '1' && count($attachments) > 0)
            || (defined('CRON') && !is_staff_logged_in())) {
            return parent::send();
        }

        $date = date("Y-m-d H:i:s");
        $to = is_array($this->_recipients) ? implode(", ", $this->_recipients) : $this->_recipients;
        $cc = implode(", ", $this->_cc_array);
        $bcc = implode(", ", $this->_bcc_array);

        $dbdata = array(
            'email' => $to,
            'cc' => $cc,
            'bcc' => $bcc,
            'message' => $this->_body,
            'alt_message' => $this->_get_alt_message(),
            'headers' => serialize($this->_headers),
            'attachments'=>base64_encode(serialize($attachments)),
            'status' => 'pending',
            'date' => $date
        );

        return $this->CI->db->insert($this->email_queue_table, $dbdata);
    }

    /**
     * Send queue
     *
     * Send queue emails.
     * @return  void
     */
    public function send_queue()
    {
        $this->clean_up_old_queue();

        $this->set_status('pending');
        $emails = $this->get_queue_emails();

        $this->CI->db->where('status', 'pending');
        $this->CI->db->set('status', 'sending');
        $this->CI->db->set('date', date("Y-m-d H:i:s"));

        $this->CI->db->update($this->email_queue_table);

        foreach ($emails as $email) {
            $attachments = array();

            if ($email->attachments) {
                $attachments = unserialize(base64_decode($email->attachments));
                foreach ($attachments as $attachment) {
                    $this->_attachments[] = $attachment;
                }
            }

            $recipients = explode(", ", $email->email);
            $cc = !empty($email->cc) ? explode(", ", $email->cc) : array();
            $bcc = !empty($email->bcc) ? explode(", ", $email->bcc) : array();

            $this->_headers = unserialize($email->headers);

            if (array_key_exists('Reply-To', $this->_headers) && !empty($this->_headers['Reply-To'])) {
                $this->_replyto_flag = true;
            }

            $this->set_newline("\r\n");
            $this->set_crlf("\r\n");

            $this->to($recipients);
            $this->cc($cc);
            $this->bcc($bcc);

            $this->message($email->message);
            $this->set_alt_message($email->alt_message);

            $status = ($this->send(true) ? 'sent' : 'failed');

            $this->CI->db->where('id', $email->id);
            $this->CI->db->set('status', $status);
            $this->CI->db->set('date', date("Y-m-d H:i:s"));
            $this->CI->db->update($this->email_queue_table);
        }
    }

    /**
     * Retry failed emails
     *
     * Resend failed or expired emails
     * @return void
     */
    public function retry_queue()
    {
        $expire = (time() - (60*5));
        $date_expire = date("Y-m-d H:i:s", $expire);
        $this->CI->db->set('status', 'pending');
        $this->CI->db->where("(date < '{$date_expire}' AND status = 'sending')");
        $this->CI->db->or_where("status = 'failed'");
        $this->CI->db->update($this->email_queue_table);
        log_message('debug', 'Email queue retrying...');
    }

    /**
     * Will remove queue rows from database that are sent and are older then 1 week
     * @return null
     */
    public function clean_up_old_queue()
    {
        $this->CI->db->where('status', 'sent');
        $this->CI->db->where('date < ', date('Y-m-d H:i:s', strtotime('-1 week')));
        $this->CI->db->delete($this->email_queue_table);
    }
}
