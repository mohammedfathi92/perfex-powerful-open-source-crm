<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Get current template assets url
 * @return string Assets url
 */
function template_assets_url()
{
    return base_url('assets/themes/' . get_option('clients_default_theme')) . '/';
}

/**
 * Return active template asset path
 * @return string
 */
function template_assets_path()
{
    return 'assets/themes/' . get_option('clients_default_theme');
}

/**
 * Current theme view part
 * @param  string $name file name
 * @param  array  $data variables passed to view
 */
function get_template_part($name, $data = array(), $return = false)
{
    $CI =& get_instance();
    if ($return == true) {
        return $CI->load->view('themes/' . get_option('clients_default_theme') . '/' . 'template_parts/' . $name, $data, TRUE);
    }
    $CI->load->view('themes/' . get_option('clients_default_theme') . '/' . 'template_parts/' . $name, $data);
}

/**
 * Get all client themes in themes folder
 * @return array
 */
function get_all_client_themes()
{
    return list_folders(APPPATH . 'views/themes/');
}

/**
 * Get active client theme
 * @return mixed
 */
function active_clients_theme()
{
    $CI =& get_instance();

    $theme = get_option('clients_default_theme');
    if ($theme == '') {
        show_error('Default theme is not set');
    }
    if (!is_dir(APPPATH . 'views/themes/' . $theme)) {
        show_error('Theme does not exists');
    }
    return $theme;
}

add_action('app_customers_head','do_theme_required_head');
/**
 * Function used in the customers are in head and hook all the necessary data for full app usage
 * @param  array  $params pass params to use
 * @return void
 */
function do_theme_required_head($params = array()){
    ob_start();
    $isRTL = (is_rtl(true) ? 'true' : 'false');
    echo get_custom_fields_hyperlink_js_function();
    $locale = get_locale_key($params['language']);

    $date_format = get_option('dateformat');
    $date_format = explode('|', $date_format);
    $date_format = $date_format[0];

    ?>
    <?php if(get_option('use_recaptcha_customers_area') == 1 && get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != ''){ ?>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <?php } ?>
    <script>
        <?php if(is_staff_logged_in()){  ?>
          var admin_url = '<?php echo admin_url(); ?>';
          <?php } ?>
          var site_url = '<?php echo site_url(''); ?>';
        // Settings required for javascript
        var calendar_events_limit = "<?php echo get_option("calendar_events_limit"); ?>";
        var maximum_allowed_ticket_attachments = "<?php echo get_option("maximum_allowed_ticket_attachments"); ?>";

        var max_php_ini_upload_size_bytes  = "<?php echo file_upload_max_size(); ?>";
        var file_exceeds_maxfile_size_in_form = "<?php echo _l('file_exceeds_maxfile_size_in_form'); ?>" + ' (<?php echo bytesToSize('', file_upload_max_size()); ?>)';
        var file_exceeds_max_filesize = "<?php echo _l('file_exceeds_max_filesize'); ?>" + ' (<?php echo bytesToSize('', file_upload_max_size()); ?>)';

        var validation_extension_not_allowed = "<?php echo _l('validation_extension_not_allowed'); ?>";
        var dt_length_menu_all = "<?php echo _l('dt_length_menu_all'); ?>";

        var drop_files_here_to_upload = "<?php echo _l('drop_files_here_to_upload'); ?>";
        var browser_not_support_drag_and_drop = "<?php echo _l('browser_not_support_drag_and_drop'); ?>";
        var remove_file = "<?php echo _l('remove_file'); ?>";
        var tables_pagination_limit = "<?php echo get_option("tables_pagination_limit"); ?>";
        var date_format = "<?php echo $date_format; ?>";
        var time_format = "<?php echo get_option('time_format'); ?>";
        var default_view_calendar = "<?php echo get_option('default_view_calendar'); ?>";
        var dt_lang = <?php echo json_encode(get_datatables_language_array()); ?>;
        var discussions_lang = <?php echo json_encode(get_project_discussions_language_array()); ?>;
        var confirm_action_prompt = "<?php echo _l('confirm_action_prompt'); ?>";
        var cf_translate_input_link_tip = "<?php echo _l('cf_translate_input_link_tip'); ?>";
        var cfh_popover_templates  = {};
        var locale = '<?php echo $locale; ?>';
        var timezone = "<?php echo get_option('default_timezone'); ?>";
        var allowed_files = "<?php echo get_option('allowed_files'); ?>";
        var isRTL = '<?php echo $isRTL; ?>';
        var calendar_first_day = '<?php echo get_option('calendar_first_day'); ?>';
        var months_json = '<?php echo json_encode(array(_l('January'),_l('February'),_l('March'),_l('April'),_l('May'),_l('June'),_l('July'),_l('August'),_l('September'),_l('October'),_l('November'),_l('December'))); ?>';
        window.addEventListener('load',function(){
            custom_fields_hyperlink();
        });
    </script>
    <?php
    $contents = ob_get_contents();
    ob_end_clean();
    echo $contents;
}
