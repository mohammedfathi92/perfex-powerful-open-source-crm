<?php
defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Remove <br /> html tags from string to show in textarea with new linke
 * @param  string $text
 * @return string formatted text
 */
function clear_textarea_breaks($text)
{
    $_text  = '';
    $_text  = $text;

    $breaks = array(
        "<br />",
        "<br>",
        "<br/>",
    );

    $_text  = str_ireplace($breaks, "", $_text);
    $_text  = trim($_text);

    return $_text;
}
/**
 * Equivalent function to nl2br php function but keeps the html if found and do not ruin the formatting
 * @param  string $string
 * @return string
 */
function nl2br_save_html($string)
{
    if (! preg_match("#</.*>#", $string)) { // avoid looping if no tags in the string.
        return nl2br($string);
    }

    $string = str_replace(array("\r\n", "\r", "\n"), "\n", $string);

    $lines=explode("\n", $string);
    $output='';
    foreach ($lines as $line) {
        $line = rtrim($line);
        if (! preg_match("#</?[^/<>]*>$#", $line)) { // See if the line finished with has an html opening or closing tag
            $line .= '<br />';
        }
        $output .= $line . "\n";
    }

    return $output;
}
/**
 * Load app stylesheet based on option
 * Can load minified stylesheet and non minified
 *
 * This function also check if there is my_ prefix stylesheet to load them.
 * If in options is set to load minified files and the filename that is passed do not contain minified version the
 * original file will be used.
 *
 * @param  string $path
 * @param  string $filename
 * @return string
 */
function app_stylesheet($path, $filename)
{
    $CI = &get_instance();

    if (file_exists(FCPATH.$path.'/my_'.$filename)) {
        $filename = 'my_'.$filename;
    }

    if (get_option('use_minified_files') == 1) {
        $original_file_name = $filename;
        $_temp = explode('.', $filename);
        $last = count($_temp) -1;
        $extension = $_temp[$last];
        unset($_temp[$last]);
        $filename = '';
        foreach ($_temp as $t) {
            $filename .= $t.'.';
        }
        $filename.= 'min.'.$extension;

        if (!file_exists(FCPATH.$path.'/'.$filename)) {
            $filename = $original_file_name;
        }
    }

    if (file_exists(FCPATH.$path.'my_'.$filename)) {
        $filename = 'my_'.$filename;
    }

    if (ENVIRONMENT == 'development') {
        $latest_version = time();
    } else {
        $latest_version = get_app_version();
    }

    return '<link href="'.base_url($path.'/'.$filename.'?v='.$latest_version).'" rel="stylesheet">'.PHP_EOL;
}
/**
 * Load app script based on option
 * Can load minified stylesheet and non minified
 *
 * This function also check if there is my_ prefix stylesheet to load them.
 * If in options is set to load minified files and the filename that is passed do not contain minified version the
 * original file will be used.
 *
 * @param  string $path
 * @param  string $filename
 * @return string
 */
function app_script($path, $filename)
{
    $CI = &get_instance();

    if (get_option('use_minified_files') == 1) {
        $original_file_name = $filename;
        $_temp = explode('.', $filename);
        $last = count($_temp) -1;
        $extension = $_temp[$last];
        unset($_temp[$last]);
        $filename = '';
        foreach ($_temp as $t) {
            $filename .= $t.'.';
        }
        $filename.= 'min.'.$extension;
        if (!file_exists($path.'/'.$filename)) {
            $filename = $original_file_name;
        }
    }

    if (ENVIRONMENT == 'development') {
        $latest_version = time();
    } else {
        $latest_version = get_app_version();
    }

    return '<script src="'.base_url($path.'/'.$filename.'?v='.$latest_version).'"></script>'.PHP_EOL;
}

/**
 * Check for alerts
 * @return null
 */
function app_js_alerts()
{
    $CI = &get_instance();
    $alertclass = get_alert_class();
    if ($alertclass != '') {
        $alert_message = '';
        $alert = $CI->session->flashdata('message-'.$alertclass);
        if (is_array($alert)) {
            foreach ($alert as $alert_data) {
                $alert_message.= '<span>'.$alert_data . '</span><br />';
            }
        } else {
            $alert_message .= $alert;
        }
        echo '<script>';
        echo '$(function(){
            alert_float("'.$alertclass.'","'.$alert_message.'");
        });';
        echo '</script>';
    }

    if ($CI->session->has_userdata('system-popup')) {
        echo '<script>';
        echo '$(function(){
            var popupData = {};
            popupData.message = '.json_encode(app_happy_text($CI->session->userdata('system-popup'))).';
            system_popup(popupData);
        });';
        echo '</script>';
    }
}

/**
 * Return application version formatted
 * @return string
 */
function get_app_version()
{
    $CI = &get_instance();
    $CI->load->config('migration');

    return wordwrap($CI->config->item('migration_version'), 1, '.', true);
}

/**
 * External form common footer, eq. leads form, tickets form
 * @param  mixed $form form from database
 * @return mixed
 */
function app_external_form_footer($form)
{
    $date_format = get_option('dateformat');
    $date_format = explode('|', $date_format);
    $date_format = $date_format[0];
    $locale_key = get_locale_key($form->language);
    echo '<script src="'.base_url('assets/plugins/jquery/jquery.min.js').'"></script>'.PHP_EOL;
    echo '<script src="'.base_url('assets/plugins/bootstrap/js/bootstrap.min.js').'"></script>'.PHP_EOL;
    echo '<script src="'.base_url('assets/plugins/jquery-validation/jquery.validate.min.js').'"></script>'.PHP_EOL;
    echo '<script src="'.base_url('assets/plugins/app-build/moment.min.js').'"></script>'.PHP_EOL;
    app_select_plugin_js($locale_key);
    if ($locale_key != 'en') {
        if (file_exists(FCPATH.'assets/plugins/jquery-validation/localization/messages_'.$locale_key.'.min.js')) {
            echo '<script src="'.base_url('assets/plugins/jquery-validation/localization/messages_'.$locale_key.'.min.js').'"></script>'.PHP_EOL;
        } elseif (file_exists(FCPATH.'assets/plugins/jquery-validation/localization/messages_'.$locale_key.'_'.strtoupper($locale_key).'.min.js')) {
            echo '<script src="'.base_url('assets/plugins/jquery-validation/localization/messages_'.$locale_key.'_'.strtoupper($locale_key).'.min.js').'"></script>'.PHP_EOL;
        }
    }
    echo '<script src="'.base_url('assets/plugins/datetimepicker/jquery.datetimepicker.full.min.js').'"></script>'.PHP_EOL;
    echo '<script src="'.base_url('assets/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js').'"></script>'.PHP_EOL; ?>
    <script>
    $(function(){
     var time_format = '<?php echo get_option('time_format'); ?>';
     var date_format = '<?php echo $date_format; ?>';

     $('body').tooltip({
       selector: '[data-toggle="tooltip"]'
     });

     $('body').find('.colorpicker-input').colorpicker({
       format: "hex"
     });

     var date_picker_options = {
       format: date_format,
       timepicker: false,
       lazyInit: true,
       dayOfWeekStart: '<?php echo get_option('calendar_first_day '); ?>',
     }

    $('.datepicker').datetimepicker(date_picker_options);
     var date_time_picker_options = {
      lazyInit: true,
      scrollInput: false,
      validateOnBlur :false,
      dayOfWeekStart: '<?php echo get_option('calendar_first_day '); ?>',
    }
    if(time_format == 24){
      date_time_picker_options.format = date_format + ' H:i';
    } else {
      date_time_picker_options.format =  date_format + ' g:i A';
      date_time_picker_options.formatTime = 'g:i A';
    }
    $('.datetimepicker').datetimepicker(date_time_picker_options);

    $('body').find('select').selectpicker({
      showSubtext: true,
    });

    $.validator.addMethod('filesize', function (value, element, param) {
        return this.optional(element) || (element.files[0].size <= param)
    }, "<?php echo _l('ticket_form_validation_file_size', bytesToSize('', file_upload_max_size())); ?>");

    $.validator.addMethod( "extension", function( value, element, param ) {
        param = typeof param === "string" ? param.replace( /,/g, "|" ) : "png|jpe?g|gif";
        return this.optional( element ) || value.match( new RegExp( "\\.(" + param + ")$", "i" ) );
    }, $.validator.format( "<?php echo _l('validation_extension_not_allowed'); ?>" ) );

    $.validator.setDefaults({
     highlight: function(element) {
       $(element).closest('.form-group').addClass('has-error');
     },
     unhighlight: function(element) {
       $(element).closest('.form-group').removeClass('has-error');
     },
     errorElement: 'p',
     errorClass: 'text-danger',
     errorPlacement: function(error, element) {
       if (element.parent('.input-group').length || element.parents('.chk').length) {
         if (!element.parents('.chk').length) {
           error.insertAfter(element.parent());
         } else {
           error.insertAfter(element.parents('.chk'));
         }
       } else {
         error.insertAfter(element);
       }
     }
   });
    });
 </script>
 <?php
}

/**
 * External forms common header, eq ticket form, lead form
 * @param  mixed $form form from database
 * @return mixed
 */
function app_external_form_header($form)
{
    echo app_stylesheet('assets/css', 'reset.css');
    if (get_option('favicon') != '') {
        echo '<link href="'.base_url('uploads/company/'.get_option('favicon')).'" rel="shortcut icon">'.PHP_EOL;
    }
    echo '<link href="'.base_url('assets/plugins/roboto/roboto.css').'" rel="stylesheet">'.PHP_EOL;
    echo '<link href="'.base_url('assets/plugins/bootstrap/css/bootstrap.min.css').'" rel="stylesheet">'.PHP_EOL;
    if (is_rtl(true)) {
        echo '<link href="'.base_url('assets/plugins/bootstrap-arabic/css/bootstrap-arabic.min.css').'" rel="stylesheet">'.PHP_EOL;
    }
    echo '<link href="'.base_url('assets/plugins/datetimepicker/jquery.datetimepicker.min.css').'" rel="stylesheet">'.PHP_EOL;
    echo '<link href="'.base_url('assets/plugins/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css').'" rel="stylesheet">'.PHP_EOL;
    echo '<link href="'.base_url('assets/plugins/font-awesome/css/font-awesome.min.css').'" rel="stylesheet">'.PHP_EOL;
    echo '<link href="'.base_url('assets/plugins/bootstrap-select/css/bootstrap-select.min.css').'" rel="stylesheet">'.PHP_EOL;
    echo app_stylesheet('assets/css', 'forms.css');
    if (get_option('recaptcha_secret_key') != '' && get_option('recaptcha_site_key') != '' && $form->recaptcha == 1) {
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>".PHP_EOL;
    }
    if (file_exists(FCPATH.'assets/css/custom.css')) {
        echo '<link href="'.base_url('assets/css/custom.css').'" rel="stylesheet">'.PHP_EOL;
    }
    render_custom_styles(array('general', 'buttons'));
    do_action('app_external_form_head');
}

/**
 * Init admin head
 * @param  boolean $aside should include aside
 */
function init_head($aside = true)
{
    $CI =& get_instance();
    $CI->load->view('admin/includes/head');
    $CI->load->view('admin/includes/header', array('startedTimers'=>$CI->misc_model->get_staff_started_timers()));
    $CI->load->view('admin/includes/setup_menu');
    if ($aside == true) {
        $CI->load->view('admin/includes/aside');
    }
}
/**
 * Init admin footer/tails
 */
function init_tail()
{
    $CI =& get_instance();
    $CI->load->view('admin/includes/scripts');
}

/**
 * Get company logo from company uploads folder
 * @param  string $url     href url of image
 * @param  string $href_class Additional classes on href
 */
function get_company_logo($uri = '', $href_class = '')
{
    $company_logo = get_option('company_logo');
    $company_name = get_option('companyname');

    if ($uri == '') {
        $logoURL = site_url();
    } else {
        $logoURL = site_url($uri);
    }

    $logoURL = do_action('logo_href', $logoURL);

    if ($company_logo != '') {
        echo '<a href="' . $logoURL . '" class="' . $href_class . ' logo img-responsive"><img src="' . base_url('uploads/company/' . $company_logo) . '" class="img-responsive" alt="' . $company_name . '"></a>';
    } elseif ($company_name != '') {
        echo '<a href="' . $logoURL . '" class="' . $href_class . ' logo logo-text">' . $company_name . '</a>';
    } else {
        echo '';
    }
}

/**
 * Payment gateway logo, user can apply hook to change the logo
 * @return string
 */
function payment_gateway_logo()
{
    $url = do_action('payment_gateway_logo_url', base_url('uploads/company/' . get_option('company_logo')));
    $width = do_action('payment_gateway_logo_width', 'auto');
    $height = do_action('payment_gateway_logo_height', '34px');

    return '<img src="'.$url.'" width="'.$width.'" height="'.$height.'">';
}

/**
 * Outputs payment gateway head, commonly used
 * @param  string $title Document title
 * @return mixed
 */
function payment_gateway_head($title = 'Payment for Invoice')
{
    $html = '<!DOCTYPE html>'.PHP_EOL;
    $html .= '<html lang="en">'.PHP_EOL;
    $html .= '<head>'.PHP_EOL;
    $html .= '<meta charset="utf-8">'.PHP_EOL;
    $html .= '<meta http-equiv="X-UA-Compatible" content="IE=edge">'.PHP_EOL;
    $html .= '<meta name="viewport" content="width=device-width, initial-scale=1">'.PHP_EOL;
    $html .= '<title>'.PHP_EOL;
    $html .= $title;
    $html .= '</title>'.PHP_EOL;
    if (get_option('favicon') != '') {
        $html .= '<link href="'.base_url('uploads/company/'.get_option('favicon')).'" rel="shortcut icon">'.PHP_EOL;
    }
    $html .= app_stylesheet('assets/css', 'reset.css');
    $html .= '<link href="'.base_url().'assets/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">'.PHP_EOL;
    $html .= '<link href="'.base_url().'assets/plugins/roboto/roboto.css" rel="stylesheet">'.PHP_EOL;
    $html .= app_stylesheet('assets/css', 'bs-overides.css');
    $html .= app_stylesheet(template_assets_path().'/css', 'style.css');
    $html .= '<style>'.PHP_EOL;
    $html .= '.text-danger {
                    color: #fc2d42;
                 }'.PHP_EOL;
    $html .= '</style>'.PHP_EOL;
    $html .= do_action('payment_gateway_head');
    $html .= '</head>'.PHP_EOL;

    return $html;
}

/**
 * Used in payment gateways head, commonly used sscripts
 * @return html
 */
function payment_gateway_scripts()
{
    $html = '<script src="'.base_url().'assets/plugins/jquery/jquery.min.js"></script>'.PHP_EOL;
    $html .= '<script src="'.base_url().'assets/plugins/bootstrap/js/bootstrap.min.js"></script>'.PHP_EOL;
    $html .= '<script src="'.base_url().'assets/plugins/jquery-validation/jquery.validate.min.js"></script>'.PHP_EOL;
    $html .= '<script>
           $.validator.setDefaults({
               errorElement: \'span\',
               errorClass: \'text-danger\',
           });
        </script>'.PHP_EOL;

    $html .= do_action('payment_gateway_scripts').PHP_EOL;

    return $html;
}
/**
 * Used in payment gateways document footer
 * @return html
 */
function payment_gateway_footer()
{
    $html = do_action('payment_gateway_footer').PHP_EOL;
    $html .= '</body>'.PHP_EOL;
    $html .= '</html>'.PHP_EOL;

    return $html;
}

/**
 * Output the select plugin with locale
 * @param  string $locale current locale
 * @return mixed
 */
function app_select_plugin_js($locale = 'en')
{
    echo "<script src='".base_url('assets/plugins/app-build/bootstrap-select.min.js?v='.get_app_version())."'></script>".PHP_EOL;

    if ($locale != 'en') {
        if (file_exists(FCPATH.'assets/plugins/bootstrap-select/js/i18n/defaults-'.$locale.'.min.js')) {
            echo "<script src='".base_url('assets/plugins/bootstrap-select/js/i18n/defaults-'.$locale.'.min.js')."'></script>".PHP_EOL;
        } elseif (file_exists(FCPATH.'assets/plugins/bootstrap-select/js/i18n/defaults-'.$locale.'_'.strtoupper($locale).'.min.js')) {
            echo "<script src='".base_url('assets/plugins/bootstrap-select/js/i18n/defaults-'.$locale.'_'.strtoupper($locale).'.min.js')."'></script>".PHP_EOL;
        }
    }
}

/**
 * Output the validation plugin with locale
 * @param  string $locale current locale
 * @return mixed
 */
function app_jquery_validation_plugin_js($locale = 'en')
{
    echo "<script src='".base_url('assets/plugins/jquery-validation/jquery.validate.min.js?v='.get_app_version())."'></script>".PHP_EOL;
    if ($locale != 'en') {
        if (file_exists(FCPATH.'assets/plugins/jquery-validation/localization/messages_'.$locale.'.min.js')) {
            echo "<script src='".base_url('assets/plugins/jquery-validation/localization/messages_'.$locale.'.min.js')."'></script>".PHP_EOL;
        } elseif (file_exists(FCPATH.'assets/plugins/jquery-validation/localization/messages_'.$locale.'_'.strtoupper($locale).'.min.js')) {
            echo "<script src='".base_url('assets/plugins/jquery-validation/localization/messages_'.$locale.'_'.strtoupper($locale).'.min.js')."'></script>".PHP_EOL;
        }
    }
}

/**
 * Return staff profile image url
 * @param  mixed $staff_id
 * @param  string $type
 * @return string
 */
function staff_profile_image_url($staff_id, $type = 'small')
{
    $url = base_url('assets/images/user-placeholder.jpg');

    if ((string) $staff_id === (string) get_staff_user_id() && isset($GLOBALS['current_user'])) {
        $staff = $GLOBALS['current_user'];
    } else {
        $CI =& get_instance();
        $CI->db->select('profile_image')
        ->where('staffid', $staff_id);

        $staff = $CI->db->get('tblstaff')->row();
    }

    if ($staff) {
        if (!empty($staff->profile_image)) {
            $profileImagePath = 'uploads/staff_profile_images/' . $staff_id . '/' . $type . '_' . $staff->profile_image;
            if (file_exists($profileImagePath)) {
                $url = base_url($profileImagePath);
            }
        }
    }

    return $url;
}

/**
 * Staff profile image with href
 * @param  boolean $id        staff id
 * @param  array   $classes   image classes
 * @param  string  $type
 * @param  array   $img_attrs additional <img /> attributes
 * @return string
 */
function staff_profile_image($id, $classes = array('staff-profile-image'), $type = 'small', $img_attrs = array())
{
    $url = base_url('assets/images/user-placeholder.jpg');

    $id = trim($id);

    $_attributes = '';
    foreach ($img_attrs as $key => $val) {
        $_attributes .= $key . '=' . '"' . $val . '" ';
    }

    $blankImageFormatted = '<img src="' . $url . '" ' . $_attributes . ' class="' . implode(' ', $classes) . '" />';

    if ((string) $id === (string) get_staff_user_id() && isset($GLOBALS['current_user'])) {
        $result = $GLOBALS['current_user'];

    } else {
        $CI =& get_instance();
        $result =  $CI->object_cache->get('staff-profile-image-data-'.$id);

        if(!$result) {
            $CI->db->select('profile_image,firstname,lastname');
            $CI->db->where('staffid', $id);
            $result = $CI->db->get('tblstaff')->row();
            $CI->object_cache->add('staff-profile-image-data-'.$id, $result);
        }
    }

    if (!$result) {
        return $blankImageFormatted;
    }

    if ($result && $result->profile_image !== null) {
        $profileImagePath = 'uploads/staff_profile_images/' . $id . '/' . $type . '_' . $result->profile_image;
        if (file_exists($profileImagePath)) {
            $profile_image = '<img ' . $_attributes . ' src="' . base_url($profileImagePath) . '" class="' . implode(' ', $classes) . '" alt="' . $result->firstname . ' ' . $result->lastname . '" />';
        } else {
            return $blankImageFormatted;
        }
    } else {
        $profile_image = '<img src="' . $url . '" ' . $_attributes . ' class="' . implode(' ', $classes) . '" alt="' . $result->firstname . ' ' . $result->lastname . '" />';
    }

    return $profile_image;
}

/**
 * Generate small icon button / font awesome
 * @param  string $url        href url
 * @param  string $type       icon type
 * @param  string $class      button class
 * @param  array  $attributes additional attributes
 * @return string
 */
function icon_btn($url = '', $type = '', $class = 'btn-default', $attributes = array())
{
    $_attributes = '';
    foreach ($attributes as $key => $val) {
        $_attributes .= $key . '=' . '"' . $val . '" ';
    }
    $_url = '#';
    if (_startsWith($url, 'http')) {
        $_url = $url;
    } elseif ($url !== '#') {
        $_url = admin_url($url);
    }

    return '<a href="' . $_url . '" class="btn ' . $class . ' btn-icon" ' . $_attributes . '><i class="fa fa-' . $type . '"></i></a>';
}

/**
 * Strip tags
 * @param  string $html string to strip tags
 * @return string
 */
function _strip_tags($html)
{
    return strip_tags($html, '<br>,<em>,<p>,<ul>,<ol>,<li>,<h4><h3><h2><h1>,<pre>,<code>,<a>,<img>,<strong>,<b>,<blockquote>,<table>,<thead>,<th>,<tr>,<td>,<tbody>,<tfoot>');
}

add_action('new_ticket_admin_page_loaded','ticket_message_save_as_predefined_reply_javascript');
add_action('ticket_admin_single_page_loaded','ticket_message_save_as_predefined_reply_javascript');

function ticket_message_save_as_predefined_reply_javascript(){
    if(!is_admin() && get_option('staff_members_save_tickets_predefined_replies') == '0') {
        return false;
    }
    ?>
    <div class="modal fade" id="savePredefinedReplyFromMessageModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><?php echo _l('predefined_replies_dt_name'); ?></h4>
      </div>
      <div class="modal-body">
        <?php echo render_input('name','predefined_reply_add_edit_name'); ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="button" class="btn btn-info" id="saveTicketMessagePredefinedReply"><?php echo _l('submit'); ?></button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
    <script>
        $(function(){
            var editorMessage = tinymce.get('message');
            if(typeof(editorMessage) != 'undefined') {
                editorMessage.on('change',function(){
                    if(editorMessage.getContent().trim() != '') {
                        if($('#savePredefinedReplyFromMessage').length == 0){
                            $('[app-field-wrapper="message"] [role="menubar"] .mce-container-body:first').append("<a id=\"savePredefinedReplyFromMessage\" data-toggle=\"modal\" data-target=\"#savePredefinedReplyFromMessageModal\" class=\"save_predefined_reply_from_message pointer\" href=\"#\"><?php echo _l('save_message_as_predefined_reply'); ?></a>");
                            }
                    } else {
                        $('#savePredefinedReplyFromMessage').remove();
                    }
                });
            }
            $('body').on('click','#saveTicketMessagePredefinedReply',function(e){
                e.preventDefault();
                var data = {}
                data.message = editorMessage.getContent();
                data.name = $('#savePredefinedReplyFromMessageModal #name').val();
                data.ticket_area = true;
                $.post(admin_url+'tickets/predefined_reply',data).done(function(response){
                    response = JSON.parse(response);
                    if(response.success == true) {
                        var predefined_reply_select = $('#insert_predefined_reply');
                        predefined_reply_select.find('option:first').after('<option value="'+response.id+'">'+data.name+'</option>');
                        predefined_reply_select.selectpicker('refresh');
                    }
                    $('#savePredefinedReplyFromMessageModal').modal('hide');
                });
            });
        });
    </script>
    <?php
}
