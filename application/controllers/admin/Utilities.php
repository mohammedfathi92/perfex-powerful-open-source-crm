<?php
defined('BASEPATH') or exit('No direct script access allowed');

@ini_set('memory_limit', '256M');
@ini_set('max_execution_time', 360);

class Utilities extends Admin_controller
{
    public $pdf_zip;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('utilities_model');
    }

    /* All perfex activity log */
    public function activity_log()
    {
        // Only full admin have permission to activity log
        if (!is_admin()) {
            access_denied('activityLog');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('activity_log');
        }
        $data['title'] = _l('utility_activity_log');
        $this->load->view('admin/utilities/activity_log', $data);
    }

    /* All perfex activity log */
    public function pipe_log()
    {
        // Only full admin have permission to activity log
        if (!is_admin()) {
            access_denied('Ticket Pipe Log');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('ticket_pipe_log');
        }
        $data['title'] = _l('ticket_pipe_log');
        $this->load->view('admin/utilities/ticket_pipe_log', $data);
    }

    public function clear_activity_log()
    {
        if (!is_admin()) {
            access_denied('Clear activity log');
        }
        $this->db->empty_table('tblactivitylog');
        redirect(admin_url('utilities/activity_log'));
    }

    public function clear_pipe_log()
    {
        if (!is_admin()) {
            access_denied('Clear ticket pipe activity log');
        }
        $this->db->empty_table('tblticketpipelog');
        redirect(admin_url('utilities/pipe_log'));
    }

    /* Calendar functions */
    public function calendar()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $data    = $this->input->post();
            $success = $this->utilities_model->event($data);
            $message = '';
            if ($success) {
                if (isset($data['eventid'])) {
                    $message = _l('event_updated');
                } else {
                    $message = _l('utility_calendar_event_added_successfully');
                }
            }
            echo json_encode(array(
                'success' => $success,
                'message' => $message,
            ));
            die();
        }
        $data['google_ids_calendars'] = $this->misc_model->get_google_calendar_ids();
        $data['google_calendar_api']  = get_option('google_calendar_api_key');
        $data['title']                = _l('calendar');
        // To load js files
        $data['calendar_assets']      = true;
        $this->load->view('admin/utilities/calendar', $data);
    }

    public function get_calendar_data()
    {
        if ($this->input->is_ajax_request()) {
            echo json_encode($this->utilities_model->get_calendar_data(
                $this->input->post('start'),
                $this->input->post('end'),
                '',
                '',
                $this->input->post()
            ));
            die();
        }
    }

    public function view_event($id)
    {
        $data['event'] = $this->utilities_model->get_event($id);
        $this->load->view('admin/utilities/event', $data);
    }

    public function delete_event($id)
    {
        if ($this->input->is_ajax_request()) {
            $event = $this->utilities_model->get_event_by_id($id);
            if ($event->userid != get_staff_user_id() && !is_admin()) {
                echo json_encode(array(
                    'success' => false,
                ));
                die;
            }
            $success = $this->utilities_model->delete_event($id);
            $message = '';
            if ($success) {
                $message = _l('utility_calendar_event_deleted_successfully');
            }
            echo json_encode(array(
                'success' => $success,
                'message' => $message,
            ));
            die();
        }
    }

    // Moved here from version 1.0.5
    public function media()
    {
        $this->load->helper('url');
        $data['title']        = _l('media_files');
        $data['connector'] = admin_url() . '/utilities/media_connector';
        $data['media_assets'] = true;
        $this->load->view('admin/utilities/media', $data);
    }

    public function media_connector()
    {
        $media_folder = $this->app->get_media_folder();
        $mediaPath = FCPATH . $media_folder;

        if (!is_dir($mediaPath)) {
            mkdir($mediaPath);
        }

        if (!file_exists($mediaPath . '/index.html')) {
            fopen($mediaPath . '/index.html', 'w');
        }

        $this->load->helper('path');

        $root_options = array(
            'driver' => 'LocalFileSystem',
            'path' => set_realpath($media_folder),
            'URL' => site_url($media_folder) . '/',
            //'debug'=>true,
            'uploadMaxSize' => get_option('media_max_file_size_upload') . 'M',
            'accessControl' => 'access_control_media',
            'uploadDeny'=>array(
                'application/x-httpd-php',
                'application/php',
                'application/x-php',
                'text/php',
                'text/x-php',
                'application/x-httpd-php-source',
                'application/perl',
                'application/x-perl',
                'application/x-python',
                'application/python',
                'application/x-bytecode.python',
                'application/x-python-bytecode',
                'application/x-python-code',
                'wwwserver/shellcgi', // CGI
            ),
            'uploadAllow'=>array(),
            'uploadOrder' => array(
                'deny',
                'allow',
            ),
            'attributes' => array(
                array(
                    'pattern' => '/.tmb/',
                    'hidden' => true,
                ),
                array(
                    'pattern' => '/.quarantine/',
                    'hidden' => true,
                ),
                array(
                    'pattern' => '/public/',
                    'hidden' => true,
                ),
            ),
        );
        if (!is_admin()) {
            $this->db->select('media_path_slug,staffid,firstname,lastname')
            ->from('tblstaff')
            ->where('staffid', get_staff_user_id());
            $user = $this->db->get()->row();
            $path = set_realpath($media_folder . '/' . $user->media_path_slug);
            if (empty($user->media_path_slug)) {
                $this->db->where('staffid', $user->staffid);
                $slug = slug_it($user->firstname . ' ' . $user->lastname);
                $this->db->update('tblstaff', array(
                    'media_path_slug' => $slug,
                ));
                $user->media_path_slug = $slug;
                $path                  = set_realpath($media_folder . '/' . $user->media_path_slug);
            }
            if (!is_dir($path)) {
                mkdir($path);
            }
            if (!file_exists($path . '/index.html')) {
                fopen($path . '/index.html', 'w');
            }
            array_push($root_options['attributes'], array(
                'pattern' => '/.(' . $user->media_path_slug . '+)/', // Prevent deleting/renaming folder
                'read' => true,
                'write' => true,
                'locked' => true,
            ));
            $root_options['path'] = $path;
            $root_options['URL']  = site_url($media_folder . '/' . $user->media_path_slug) . '/';
        }

        $publicRootPath = $media_folder.'/public';
        $public_root = $root_options;
        $public_root['path'] = set_realpath($publicRootPath);

        $public_root['URL'] = site_url($media_folder) . '/public';
        unset($public_root['attributes'][3]);

        if (!is_dir($publicRootPath)) {
            mkdir($publicRootPath);
        }

        if (!file_exists($publicRootPath . '/index.html')) {
            fopen($publicRootPath . '/index.html', 'w');
        }

        $opts = array(
            'roots' => array(
                $root_options,
                $public_root,
            ),
        );

        $opts = do_action('before_init_media', $opts);
        $connector = new elFinderConnector(new elFinder($opts));
        $connector->run();
    }

    public function bulk_pdf_exporter()
    {
        if (!has_permission('bulk_pdf_exporter', '', 'view')) {
            access_denied('bulk_pdf_exporter');
        }

        $has_permission_estimates_view = has_permission('estimates', '', 'view');
        $has_permission_invoices_view  = has_permission('invoices', '', 'view');
        $has_permission_proposals_view = has_permission('proposals', '', 'view');
        $has_permission_payments_view  = has_permission('payments', '', 'view');
        $has_permission_credit_notes_view  = has_permission('credit_notes', '', 'view');

        if ($this->input->post()) {
            if (!is_really_writable(TEMP_FOLDER)) {
                show_error('/temp folder is not writable. You need to change the permissions to 755');
            }
            $type = $this->input->post('export_type');
            if ($type == 'invoices') {
                $status = $this->input->post('invoice_export_status');
                $this->db->select('id');
                $this->db->from('tblinvoices');
                if ($status != 'all') {
                    $this->db->where('status', $status);
                }

                if (!$has_permission_invoices_view) {
                    $this->db->where('addedfrom', get_staff_user_id());
                }

                $this->db->order_by('date', 'desc');
            } elseif ($type == 'estimates') {
                $status = $this->input->post('estimate_export_status');
                $this->db->select('id');
                $this->db->from('tblestimates');
                if ($status != 'estimates_all') {
                    $this->db->where('status', $status);
                }

                if (!$has_permission_estimates_view) {
                    $this->db->where('addedfrom', get_staff_user_id());
                }
                $this->db->order_by('date', 'desc');
            } elseif ($type == 'credit_notes') {
                $status = $this->input->post('credit_notes_status_export');
                $this->db->select('id');
                $this->db->from('tblcreditnotes');

                if ($status != 'all') {
                    $this->db->where('status', $status);
                }

                if (!$has_permission_credit_notes_view) {
                    $this->db->where('addedfrom', get_staff_user_id());
                }

                $this->db->order_by('date', 'desc');
            } elseif ($type == 'payments') {
                $this->db->select('tblinvoicepaymentrecords.id as paymentid');
                $this->db->from('tblinvoicepaymentrecords');
                $this->db->join('tblinvoices', 'tblinvoices.id = tblinvoicepaymentrecords.invoiceid', 'left');
                $this->db->join('tblclients', 'tblclients.userid = tblinvoices.clientid', 'left');

                if (!$has_permission_payments_view) {
                    $this->db->where('invoiceid IN (SELECT id FROM tblinvoices WHERE addedfrom=' . get_staff_user_id() . ')');
                }
                if ($this->input->post('paymentmode')) {
                    $this->db->where('paymentmode', $this->input->post('paymentmode'));
                }
            } elseif ($type == 'proposals') {
                $this->db->select('id');
                $this->db->from('tblproposals');
                $status = $this->input->post('proposal_export_status');
                if ($status != 'all') {
                    $this->db->where('status', $status);
                }

                if (!$has_permission_proposals_view) {
                    $this->db->where('addedfrom', get_staff_user_id());
                }

                $this->db->order_by('date', 'desc');
            } else {
                // This may not happend but in all cases :)
                die('No Export Type Selected');
            }
            if ($this->input->post('date-to') && $this->input->post('date-from')) {
                $from_date  = to_sql_date($this->input->post('date-from'));
                $to_date    = to_sql_date($this->input->post('date-to'));
                $date_field = 'date';
                // Column date is ambiguous in payments
                if ($type == 'payments') {
                    $date_field = 'tblinvoicepaymentrecords.date';
                }
                if ($from_date == $to_date) {
                    $this->db->where($date_field, $from_date);
                } else {
                    $this->db->where($date_field . ' BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                }
            }
            $data = $this->db->get()->result_array();

            if (count($data) == 0) {
                set_alert('warning', _l('no_data_found_bulk_pdf_export'));
                redirect(admin_url('utilities/bulk_pdf_exporter'));
            }
            $dir = TEMP_FOLDER . $type;
            if (is_dir($dir)) {
                delete_dir($dir);
            }
            mkdir($dir, 0777);
            if ($type == 'invoices') {
                $this->load->model('invoices_model');
                foreach ($data as $invoice) {
                    $invoice_data    = $this->invoices_model->get($invoice['id']);
                    $this->pdf_zip   = invoice_pdf($invoice_data, $this->input->post('tag'));
                    $_temp_file_name = slug_it(format_invoice_number($invoice_data->id));
                    $file_name       = $dir . '/' . strtoupper($_temp_file_name);
                    $this->pdf_zip->Output($file_name . '.pdf', 'F');
                }
            } elseif ($type == 'credit_notes') {
                $this->load->model('credit_notes_model');
                foreach ($data as $credit_note) {
                    $credit_note_data    = $this->credit_notes_model->get($credit_note['id']);
                    $this->pdf_zip   = credit_note_pdf($credit_note_data, $this->input->post('tag'));
                    $_temp_file_name = slug_it(format_credit_note_number($credit_note_data->id));
                    $file_name       = $dir . '/' . strtoupper($_temp_file_name);
                    $this->pdf_zip->Output($file_name . '.pdf', 'F');
                }
            } elseif ($type == 'estimates') {
                foreach ($data as $estimate) {
                    $this->load->model('estimates_model');
                    $estimate_data   = $this->estimates_model->get($estimate['id']);
                    $this->pdf_zip   = estimate_pdf($estimate_data, $this->input->post('tag'));
                    $_temp_file_name = slug_it(format_estimate_number($estimate_data->id));
                    $file_name       = $dir . '/' . strtoupper($_temp_file_name);
                    $this->pdf_zip->Output($file_name . '.pdf', 'F');
                }
            } elseif ($type == 'payments') {
                $this->load->model('payments_model');
                $this->load->model('invoices_model');
                foreach ($data as $payment) {
                    $payment_data               = $this->payments_model->get($payment['paymentid']);
                    $payment_data->invoice_data = $this->invoices_model->get($payment_data->invoiceid);
                    $this->pdf_zip              = payment_pdf($payment_data, $this->input->post('tag'));
                    $file_name                  = $dir;
                    $file_name .= '/' . strtoupper(_l('payment'));
                    $file_name .= '-' . strtoupper($payment_data->paymentid) . '.pdf';
                    $this->pdf_zip->Output($file_name, 'F');
                }
            } else {
                $this->load->model('proposals_model');
                foreach ($data as $proposal) {
                    $proposal        = $this->proposals_model->get($proposal['id']);
                    $this->pdf_zip   = proposal_pdf($proposal, $this->input->post('tag'));
                    $_temp_file_name = slug_it($proposal->subject);
                    $file_name       = $dir . '/' . strtoupper($_temp_file_name);
                    $this->pdf_zip->Output($file_name . '.pdf', 'F');
                }
            }
            $this->load->library('zip');
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the export type
            delete_dir($dir);
            $this->zip->download(slug_it(get_option('companyname')) . '-' . $type . '.zip');
            $this->zip->clear_data();
        }
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get();

        $this->load->model('invoices_model');
        $data['invoice_statuses'] = $this->invoices_model->get_statuses();

        $this->load->model('credit_notes_model');
        $data['credit_notes_statuses'] = $this->credit_notes_model->get_statuses();

        $this->load->model('proposals_model');
        $data['proposal_statuses'] = $this->proposals_model->get_statuses();

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();

        $data['title'] = _l('bulk_pdf_exporter');
        $this->load->view('admin/utilities/bulk_pdf_exporter', $data);
    }

    /* Database back up functions */
    public function backup()
    {
        if (!is_admin()) {
            access_denied('databaseBackup');
        }
        $data['title'] = _l('utility_backup');
        $this->load->view('admin/utilities/backup', $data);
    }

    public function make_backup_db()
    {
        do_action('before_make_backup');
        // Only full admin can make database backup
        if (!is_admin()) {
            access_denied('databaseBackup');
        }
        if (!is_really_writable(BACKUPS_FOLDER)) {
            show_error('/backups folder is not writable. You need to change the permissions to 755');
        }
        $this->load->model('cron_model');
        $success = $this->cron_model->make_backup_db(true);
        if ($success) {
            set_alert('success', _l('backup_success'));
        }
        redirect(admin_url('utilities/backup'));
    }

    public function update_auto_backup_options()
    {
        do_action('before_update_backup_options');
        if (!is_admin()) {
            access_denied('databaseBackup');
        }
        if ($this->input->post()) {
            $_post     = $this->input->post();
            $updated_1 = update_option('auto_backup_enabled', $_post['settings']['auto_backup_enabled']);
            $updated_2 = update_option('auto_backup_every', $this->input->post('auto_backup_every'));
            $updated_3 = update_option('delete_backups_older_then', $this->input->post('delete_backups_older_then'));
            if ($updated_2 || $updated_1 || $updated_3) {
                set_alert('success', _l('auto_backup_options_updated'));
            }
        }
        redirect(admin_url('utilities/backup'));
    }

    public function delete_backup($backup)
    {
        if (!is_admin()) {
            access_denied('databaseBackup');
        }
        if (unlink(BACKUPS_FOLDER . $backup)) {
            set_alert('success', _l('backup_delete'));
        }
        redirect(admin_url('utilities/backup'));
    }

    public function theme_style()
    {
        $data['title'] = _l('theme_style');
        $this->load->view('admin/utilities/theme_style', $data);
    }

    public function save_theme_style()
    {
        do_action('before_save_theme_style');
        $data = $this->input->post();
        if ($data == null) {
            $data = array();
        } else {
            $data = $data['data'];
        }

        update_option('theme_style', $data);
    }

    public function main_menu()
    {
        if (!is_admin()) {
            access_denied('Edit Main Menu');
        }
        $data['permissions']   = $this->roles_model->get_permissions();
        $data['permissions'][] = array(
            'shortname' => 'is_admin',
            'name' => 'Admin',
        );
        $data['permissions'][] = array(
            'shortname' => 'is_not_staff',
            'name' => _l('is_not_staff_member'),
        );

        $data['title']                 = _l('main_menu');
        $this->load->view('admin/utilities/main_menu', $data);
    }

    public function update_aside_menu()
    {
        do_action('before_update_aside_menu');
        if (!is_admin()) {
            access_denied('Edit Main Menu');
        }
        $data_inactive = $this->input->post('inactive');
        if ($data_inactive == null) {
            $data_inactive = array();
        }
        $data_active = $this->input->post('active');
        if ($data_active == null) {
            $data_active = array();
        }
        update_option('aside_menu_active', json_encode(array(
            'aside_menu_active' => $data_active,
        )));
        update_option('aside_menu_inactive', json_encode(array(
            'aside_menu_inactive' => $data_inactive,
        )));
    }

    public function reset_aside_menu()
    {
        if (is_admin()) {
            update_option('aside_menu_active', default_aside_menu_active());
            update_option('aside_menu_inactive', '{"aside_menu_inactive":[]}');
        }
        redirect(admin_url('utilities/main_menu'));
    }

    public function setup_menu()
    {
        if (!is_admin()) {
            access_denied('Edit Setup Menu');
        }
        $data['permissions']           = $this->roles_model->get_permissions();
        $data['permissions'][]         = array(
            'shortname' => 'is_admin',
            'name' => 'Admin',
        );
        $data['permissions'][]         = array(
            'shortname' => 'is_not_staff',
            'name' => _l('is_not_staff_member'),
        );
        $data['title']                 = _l('setup_menu');
        $this->load->view('admin/utilities/setup_menu', $data);
    }

    public function update_setup_menu()
    {
        do_action('before_update_setup_menu');
        if (!is_admin()) {
            access_denied('Edit Setup Menu');
        }
        $data_inactive = $this->input->post('inactive');
        if ($data_inactive == null) {
            $data_inactive = array();
        }
        $data_active = $this->input->post('active');
        if ($data_active == null) {
            $data_active = array();
        }
        update_option('setup_menu_active', json_encode(array(
            'setup_menu_active' => $data_active,
        )));
        update_option('setup_menu_inactive', json_encode(array(
            'setup_menu_inactive' => $data_inactive,
        )));
    }

    public function reset_setup_menu()
    {
        if (is_admin()) {
            update_option('setup_menu_active', default_setup_menu_active());
            update_option('setup_menu_inactive', '{"setup_menu_inactive":[]}');
        }
        redirect(admin_url('utilities/setup_menu'));
    }
}
