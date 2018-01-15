<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
class Perfex_Base
{
    private $options = array();
    // Quick actions aide
    private $quick_actions = array();
    // Instance CI
    private $_instance;
    // Show or hide setup menu
    private $show_setup_menu = true;
    // Currently reminders
    private $available_reminders = array('customer', 'lead', 'estimate', 'invoice', 'proposal', 'expense', 'credit_note');
    // Tables where currency id is used
    private $tables_with_currency = array();
    // Media folder
    private $media_folder;
    // Available languages
    private $available_languages = array();

    public function __construct()
    {
        $this->_instance =& get_instance();

        // Temporary checking for v1.8.0
        if ($this->_instance->db->field_exists('autoload', 'tbloptions')) {
            $options = $this->_instance->db->select('name, value')
            ->where('autoload', 1)
            ->get('tbloptions')->result_array();
        } else {
            $options = $this->_instance->db->select('name, value')
            ->get('tbloptions')->result_array();
        }

        // Loop the options and store them in a array to prevent fetching again and again from database
        foreach ($options as $option) {
            $this->options[$option['name']] = $option['value'];
        }

        $this->tables_with_currency = do_action('tables_with_currency', array(
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
        ));

        $this->media_folder = do_action('before_set_media_folder', 'media');

        foreach (list_folders(APPPATH . 'language') as $language) {
            if (is_dir(APPPATH.'language/'.$language)) {
                array_push($this->available_languages, $language);
            }
        }

        do_action('app_base_after_construct_action');
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
            $this->_instance->db->select('value');
            $this->_instance->db->where('name', $name);
            $row = $this->_instance->db->get('tbloptions')->row();
            if ($row) {
                $val = $row->value;
            }
        } else {
            $val = $this->options[$name];
        }

        $hook_data = do_action('get_option',array('name'=>$name,'value'=>$val));
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
     * Predefined contact permission
     * @return array
     */
    public function get_contact_permissions()
    {
        $permissions = array(
            array(
                'id' => 1,
                'name' => _l('customer_permission_invoice'),
                'short_name' => 'invoices',
            ),
            array(
                'id' => 2,
                'name' => _l('customer_permission_estimate'),
                'short_name' => 'estimates',
            ),
            array(
                'id' => 3,
                'name' => _l('customer_permission_contract'),
                'short_name' => 'contracts',
            ),
            array(
                'id' => 4,
                'name' => _l('customer_permission_proposal'),
                'short_name' => 'proposals',
            ),
            array(
                'id' => 5,
                'name' => _l('customer_permission_support'),
                'short_name' => 'support',
            ),
            array(
                'id' => 6,
                'name' => _l('customer_permission_projects'),
                'short_name' => 'projects',
            ),
        );

        return do_action('get_contact_permissions', $permissions);
    }

    /**
     * Aside.php will set the menu visibility here based on few conditions
     * @param int $total_setup_menu_items total setup menu items shown to the user
     */
    public function set_setup_menu_visibility($total_setup_menu_items)
    {
        if ($total_setup_menu_items == 0) {
            $this->show_setup_menu = false;
        } else {
            $this->show_setup_menu = true;
        }
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
        return do_action('tables_with_currencies', $this->tables_with_currency);
    }

    /**
     * Return the media folder name
     * @return string
     */
    public function get_media_folder()
    {
        return do_action('get_media_folder', $this->media_folder);
    }
}
