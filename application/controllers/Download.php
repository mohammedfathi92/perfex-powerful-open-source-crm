<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Download extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('download');
    }

    public function preview_video(){

        $path = FCPATH . $this->input->get('path');
        $file_type          = $this->input->get('type');

        $allowed_extensions = get_html5_video_extensions();

        $pathinfo           = pathinfo($path);

        if (!file_exists($path) || !isset($pathinfo['extension']) || !in_array($pathinfo['extension'], $allowed_extensions)) {
            $file_type = 'image/jpg';
            $path      = FCPATH . 'assets/images/preview-not-available.jpg';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Type: ' . $file_type);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        do_action('before_output_preview_video');

        $file = fopen($path, 'rb');
        if ( $file !== false ) {
            while ( !feof($file) ) {
                echo fread($file, 1024);
            }
            fclose($file);
        }
    }

    public function preview_image()
    {
        $path               = FCPATH . $this->input->get('path');
        $file_type          = $this->input->get('type');

        $allowed_extensions = array(
            'jpg',
            'jpeg',
            'png',
            'bmp',
            'gif',
            'tif'
        );

        $pathinfo           = pathinfo($path);

        if (!file_exists($path) || !isset($pathinfo['extension']) || !in_array($pathinfo['extension'], $allowed_extensions)) {
            $file_type = 'image/jpg';
            $path      = FCPATH . 'assets/images/preview-not-available.jpg';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        header('Content-Type: ' . $file_type);
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        do_action('before_output_preview_image');
        $file = fopen($path, 'rb');
        if ( $file !== false ) {
            while ( !feof($file) ) {
                echo fread($file, 1024);
            }
            fclose($file);
        }
    }

    public function file($folder_indicator, $attachmentid = '')
    {
        $this->load->model('tickets_model');
        if ($folder_indicator == 'ticket') {
            if (is_logged_in()) {
                $this->db->where('id', $attachmentid);
                $attachment = $this->db->get('tblticketattachments')->row();
                if (!$attachment) {
                    die('No attachment found in database');
                }
                $ticket   = $this->tickets_model->get_ticket_by_id($attachment->ticketid);
                $ticketid = $attachment->ticketid;
                if ($ticket->userid == get_client_user_id() || is_staff_logged_in()) {
                    if ($attachment->id != $attachmentid) {
                        die('Attachment or ticket not equal');
                    }
                    $path = get_upload_path_by_type('ticket') . $ticketid . '/' . $attachment->file_name;
                }
            }
        } elseif ($folder_indicator == 'newsfeed') {
            if (is_logged_in()) {
                if (!$attachmentid) {
                    die('No attachmentid specified');
                }
                $this->db->where('id', $attachmentid);
                $attachment = $this->db->get('tblfiles')->row();
                if (!$attachment) {
                    die('No attachment found in database');
                }
                $path = get_upload_path_by_type('newsfeed') . $attachment->rel_id . '/' . $attachment->file_name;
            }
        } elseif ($folder_indicator == 'contract') {
            if (is_logged_in()) {
                if (!$attachmentid) {
                    die('No attachmentid specified');
                }
                $this->db->where('id', $attachmentid);
                $attachment = $this->db->get('tblfiles')->row();
                if (!$attachment) {
                    die('No attachment found in database');
                }
                $this->load->model('contracts_model');
                $contract = $this->contracts_model->get($attachment->rel_id);
                if (is_client_logged_in()) {
                    if ($contract->not_visible_to_client == 1) {
                        if (!is_staff_logged_in()) {
                            die;
                        }
                    }
                }
                if (!is_staff_logged_in()) {
                    if ($contract->client != get_client_user_id()) {
                        die();
                    }
                } else {
                    if (!has_permission('contracts', '', 'view') && !has_permission('contracts', '', 'view_own')) {
                        access_denied('contracts');
                    }
                }
                $path = get_upload_path_by_type('contract') . $attachment->rel_id . '/' . $attachment->file_name;
            }
        } elseif ($folder_indicator == 'taskattachment') {
            if (!is_staff_logged_in() && !is_client_logged_in()) {
                die();
            }

            $this->db->where('id', $attachmentid);
            $attachment = $this->db->get('tblfiles')->row();
            if (!$attachment) {
                die('No attachment found in database');
            }
            $path = get_upload_path_by_type('task') . $attachment->rel_id . '/' . $attachment->file_name;
        } elseif ($folder_indicator == 'sales_attachment') {
            $this->db->where('attachment_key', $attachmentid);
            $attachment = $this->db->get('tblfiles')->row();
            if (!$attachment) {
                die('No attachment found in database');
            }

            $path = get_upload_path_by_type($attachment->rel_type) . $attachment->rel_id . '/' . $attachment->file_name;
        } elseif ($folder_indicator == 'expense') {
            if (!is_staff_logged_in()) {
                die();
            }
            $this->db->where('rel_id', $attachmentid);
            $this->db->where('rel_type', 'expense');
            $file = $this->db->get('tblfiles')->row();
            $path = get_upload_path_by_type('expense') . $file->rel_id . '/' . $file->file_name;
        } elseif ($folder_indicator == 'lead_attachment') {
            if (!is_staff_logged_in()) {
                die();
            }
            $this->db->where('id', $attachmentid);
            $attachment = $this->db->get('tblfiles')->row();
            if (!$attachment) {
                die('No attachment found in database');
            }
            $path = get_upload_path_by_type('lead') . $attachment->rel_id . '/' . $attachment->file_name;
        } elseif ($folder_indicator == 'db_backup') {
            if (!is_admin()) {
                die('Access forbidden');
            }
            $path = BACKUPS_FOLDER . $attachmentid;
        } elseif ($folder_indicator == 'client') {
            if (!is_client_logged_in()) {
                $this->db->where('id', $attachmentid);
            } else {
                $this->db->where('attachment_key', $attachmentid);
            }
            $attachment = $this->db->get('tblfiles')->row();
            if (!$attachment) {
                die;
            }
            if (has_permission('customers', '', 'view') || is_customer_admin($attachment->rel_id) || is_client_logged_in()) {
                $path = get_upload_path_by_type('customer') . $attachment->rel_id . '/' . $attachment->file_name;
            }
        } else {
            die('folder not specified');
        }
        force_download($path, null);
    }
}
