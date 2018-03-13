<?php defined('BASEPATH') or exit('No direct script access allowed');

class App
{
    /**
     * Options autoload=1
     * @var array
     */
    private $options = array();
    /**
     * Quick actions create aside
     * @var array
     */
    private $quick_actions = array();
    /**
     * CI Instance
     * @deprecated 1.9.8 Use $this->ci instead
     * @var object
     */
    private $_instance;
    /**
     * CI Instance
     * @var object
     */
    private $ci;
    /**
     * Show or hide setup menu
     * @var boolean
     */
    private $show_setup_menu = true;
    /**
     * Available reminders
     * @var array
     */
    private $available_reminders = array('customer', 'lead', 'estimate', 'invoice', 'proposal', 'expense', 'credit_note');
    /**
     * Tables where currency id is used
     * @var array
     */
    private $tables_with_currency = array();
    /**
     * Media folder
     * @var string
     */
    private $media_folder;
    /**
     * Available languages
     * @var array
     */
    private $available_languages = array();

    public function __construct()
    {
        $this->ci =& get_instance();
        // @deprecated
        $this->_instance = $this->ci;

        $this->init();

        do_action('app_base_after_construct_action');
    }

    /**
     * Check if database upgrade is required
     * @param  string  $v
     * @return boolean
     */
    public function is_db_upgrade_required($v = '')
    {
        if (!is_numeric($v)) {
            $v = $this->get_current_db_version();
        }

        $this->ci->load->config('migration');
        if ((int) $this->ci->config->item('migration_version') !== (int) $v) {
            return true;
        }

        return false;
    }

    /**
     * Return current database version
     * @return string
     */
    public function get_current_db_version()
    {
        $this->ci->db->limit(1);

        return $this->ci->db->get('tblmigrations')->row()->version;
    }

    /**
     * Upgrade database
     * @return mixed
     */
    public function upgrade_database()
    {
        if (!is_really_writable(APPPATH . 'config/config.php')) {
            show_error('/config/config.php file is not writable. You need to change the permissions to 755. This error occurs while trying to update database to latest version.');
            die;
        }

        $update = $this->upgrade_database_silent();

        if ($update['success'] == false) {
            show_error($update['message']);
        } else {
            set_alert('success', 'Your database is up to date');

            if (is_staff_logged_in()) {
                redirect(admin_url(), 'refresh');
            } else {
                redirect(site_url('authentication/admin'));
            }
        }
    }

    /**
     * Make request to server to get latest version info
     * @return mixed
     */
    public function get_update_info()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_USERAGENT=>$this->ci->agent->agent_string(),
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_URL => UPDATE_INFO_URL,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(
                'update_info' => 'true',
                'current_version' => $this->get_current_db_version(),
            ),
        ));

        $result = curl_exec($curl);
        $error  = '';

        if (!$curl || !$result) {
            $error = 'Curl Error - Contact your hosting provider with the following error as reference: Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
        }

        curl_close($curl);

        if ($error != '') {
            return $error;
        }

        return $result;
    }

    /**
     * Return all available languages in the application/language folder
     * @return array
     */
    public function get_available_languages()
    {
        $languages = $this->available_languages;

        return do_action('before_get_languages', $languages);
    }

    /**
     * Function that will parse table data from the tables folder for amin area
     * @param  string $table  table filename
     * @param  array  $params additional params
     * @return void
     */
    public function get_table_data($table, $params = array())
    {
        $hook_data = do_action('before_render_table_data', array(
            'table' => $table,
            'params' => $params,
        ));

        foreach ($hook_data['params'] as $key => $val) {
            $$key = $val;
        }

        $table = $hook_data['table'];

        $customFieldsColumns = array();

        if (file_exists(VIEWPATH . 'admin/tables/my_' . $table . '.php')) {
            include_once(VIEWPATH . 'admin/tables/my_' . $table . '.php');
        } else {
            include_once(VIEWPATH . 'admin/tables/' . $table . '.php');
        }

        echo json_encode($output);
        die;
    }

    /**
     * All available reminders keys for the features
     * @return array
     */
    public function get_available_reminders_keys()
    {
        return $this->available_reminders;
    }

    /**
     * Get all db options
     * @return array
     */
    public function get_options()
    {
        return $this->options;
    }

    /**
     * Function that gets option based on passed name
     * @param  string $name
     * @return string
     */
    public function get_option($name)
    {
        if ($name == 'number_padding_invoice_and_estimate') {
            $name = 'number_padding_prefixes';
        }

        $val = '';
        $name = trim($name);

        if (!isset($this->options[$name])) {
            // is not auto loaded
            $this->ci->db->select('value');
            $this->ci->db->where('name', $name);
            $row = $this->ci->db->get('tbloptions')->row();
            if ($row) {
                $val = $row->value;
            }
        } else {
            $val = $this->options[$name];
        }

        $hook_data = do_action('get_option', array('name'=>$name, 'value'=>$val));

        return $hook_data['value'];
    }

    /**
     * Add new quick action data
     * @param array $item
     */
    public function add_quick_actions_link($item = array())
    {
        $this->quick_actions[] = $item;
    }

    /**
     * Quick actions data set from admin_controller.php
     * @return array
     */
    public function get_quick_actions_links()
    {
        $this->quick_actions = do_action('before_build_quick_actions_links', $this->quick_actions);

        return $this->quick_actions;
    }

    /**
     * Aside.php will set the menu visibility here based on few conditions
     * @param int $total_setup_menu_items total setup menu items shown to the user
     */
    public function set_setup_menu_visibility($total_setup_menu_items)
    {
        $this->show_setup_menu = $total_setup_menu_items == 0 ? false : true;
    }

    /**
     * Check if should the script show the setup menu or not
     * @return boolean
     */
    public function show_setup_menu()
    {
        return do_action('show_setup_menu', $this->show_setup_menu);
    }

    /**
     * Return tables that currency id is used
     * @return array
     */
    public function get_tables_with_currency()
    {
        return do_action('tables_with_currency', $this->tables_with_currency);
    }

    /**
     * Return the media folder name
     * @return string
     */
    public function get_media_folder()
    {
        return do_action('get_media_folder', $this->media_folder);
    }

    /**
     * Upgrade database without throwing any errors
     * @return mixed
     */
    private function upgrade_database_silent()
    {
        $this->ci->load->config('migration');

        $beforeUpdateVersion = $this->get_current_db_version();

        $this->ci->load->library('migration', array(
            'migration_enabled' => true,
            'migration_type' => $this->ci->config->item('migration_type'),
            'migration_table' => $this->ci->config->item('migration_table'),
            'migration_auto_latest' => $this->ci->config->item('migration_auto_latest'),
            'migration_version' => $this->ci->config->item('migration_version'),
            'migration_path' => $this->ci->config->item('migration_path'),
        ));
        if ($this->ci->migration->current() === false) {
            return array(
                'success' => false,
                'message' => $this->ci->migration->error_string(),
            );
        } else {
            update_option('upgraded_from_version', $beforeUpdateVersion);

            return array(
                'success' => true,
            );
        }
    }

    /**
     * Init necessary data
     */
    protected function init()
    {
        // Temporary checking for v1.8.0
        if ($this->ci->db->field_exists('autoload', 'tbloptions')) {
            $options = $this->ci->db->select('name, value')
            ->where('autoload', 1)
            ->get('tbloptions')->result_array();
        } else {
            $options = $this->ci->db->select('name, value')
            ->get('tbloptions')->result_array();
        }

        // Loop the options and store them in a array to prevent fetching again and again from database
        foreach ($options as $option) {
            $this->options[$option['name']] = $option['value'];
        }

        /**
         * Available languages
         */
        foreach (list_folders(APPPATH . 'language') as $language) {
            if (is_dir(APPPATH.'language/'.$language)) {
                array_push($this->available_languages, $language);
            }
        }

        /**
         * Media folder
         * @var string
         */
        $this->media_folder = do_action('before_set_media_folder', 'media');

        /**
         * Tables with currency
         * @var array
         */
        $this->tables_with_currency = array(
            array(
                'table' => 'tblinvoices',
                'field' => 'currency',
            ),
            array(
                'table' => 'tblexpenses',
                'field' => 'currency',
            ),
            array(
                'table' => 'tblproposals',
                'field' => 'currency',
            ),
            array(
                'table' => 'tblestimates',
                'field' => 'currency',
            ),
            array(
                'table' => 'tblclients',
                'field' => 'default_currency',
            ),
            array(
                'table' => 'tblcreditnotes',
                'field' => 'currency',
            ),
        );
    }

    /**
     * Predefined contact permission
     * @deprecated 1.9.8 use get_contact_permissions() instead
     * @return array
     */
    public function get_contact_permissions()
    {
        return get_contact_permissions();
    }
}
