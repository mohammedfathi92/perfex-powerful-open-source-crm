<?php
defined('BASEPATH') or exit('No direct script access allowed');

if(!defined('DISABLE_APP_SYSTEM_HELP_MESSAGES') || (defined('DISABLE_APP_SYSTEM_HELP_MESSAGES') && DISABLE_APP_SYSTEM_HELP_MESSAGES)){

    add_action('ticket_created', '_system_popup_message_ticket_form');
    add_action('lead_created', '_system_popup_message_web_to_lead_form');
    add_action('new_tag_created', '_system_popup_message_tags_styling');
    add_action('task_timer_started', '_system_popup_message_timers_with_no_task');
    add_action('smtp_test_email_success', '_system_popup_message_email_configured');
    add_action('task_checklist_item_created', '_system_popup_task_checklist_items_drag_ability');

}

function _maybe_system_setup_warnings()
{
    // Check for just updates message
    add_action('before_start_render_dashboard_content', '_show_just_updated_message');
    // Show development message
    add_action('before_start_render_dashboard_content', '_show_development_mode_message');
    // Check if cron is required to be configured for some features
    add_action('before_start_render_dashboard_content', '_is_cron_setup_required');
    // Check if timezone is set
    add_action('before_start_render_dashboard_content', '_maybe_timezone_not_set');
    // Notice for cloudflare rocket loader
    add_action('before_start_render_dashboard_content', '_maybe_using_cloudflare_rocket_loader');
    // Notice for iconv extension
    add_action('before_start_render_dashboard_content', '_maybe_iconv_needs_to_be_enabled');
    // Check if there is dot in database name, causing problem on upgrade
    add_action('before_start_render_dashboard_content', '_maybe_dot_in_database_name');
}

/**
 * Check if there is dot in database name and throws warning message.
 * @return void
 */
function _maybe_dot_in_database_name()
{
    if (defined('APP_DB_NAME') && strpos(APP_DB_NAME, '.') !== false) {
        ?>
        <div class="col-md-12">
            <div class="alert alert-warning">
                <h4>Database name (<?php echo APP_DB_NAME; ?>) change required.</h4>
                The system indicated that your database name contains <b>. (dot)</b>, you can encounter upgrading errors when your database name contains dot, it's highly recommended to change your database name to be without dot as example: <?php echo str_replace('.', '', APP_DB_NAME); ?>
                <hr />
                <ul>
                    <li>1. Change the name to be without dot via cPanel/Command line or contact your hosting provider/server administrator to change the name. (use the best method that is suitable for you)</li>
                    <li>2. After the name is changed navigate via ftp or cPanel to application/config/app-config.php and change the database name config constant to your new database name.</li>
                    <li>3. Save the modified app-config.php file.</li>
                </ul>
                <br />
                <small>This message will disappear automatically once the database name won't contain dot.</small>
         </div>
     </div>
     <?php
    }
}


/**
 * Function that will check if iconv php extension is required based on the usage
 * @return mixed
 */
function _maybe_iconv_needs_to_be_enabled()
{
    if (!function_exists('iconv')) {
        $CI = &get_instance();
        $leadsEmailIntegrationConfigured = false;
        $imapAutoImportingTicketsConfigured = false;
        if (total_rows('tblleadsintegration', array(
            'active'=>1,
            'email !='=>'',
            'imap_server !='=>'',
            'password !='=>'',
            )) > 0) {
            $leadsEmailIntegrationConfigured = true;
        }
        if (total_rows('tbldepartments', array(
        'email !='=>'',
        'host !='=>'',
        'password !='=>''
        )) > 0) {
            $imapAutoImportingTicketsConfigured = true;
        }
        if ($imapAutoImportingTicketsConfigured || $leadsEmailIntegrationConfigured) {
            $usedFeatures = '';
            if ($imapAutoImportingTicketsConfigured && !$leadsEmailIntegrationConfigured) {
                $usedFeatures = ' auto importing tickets via the IMAP method';
            } elseif ($leadsEmailIntegrationConfigured && !$imapAutoImportingTicketsConfigured) {
                $usedFeatures = ' leads email integration feature';
            } else {
                $usedFeatures = ' auto importing tickets via the IMAP method and leads email integration features';
            } ?>
        <div class="col-md-12">
            <div class="alert alert-danger">
             You need to enable <b>iconv</b> php extension in order to use <b><?php echo $usedFeatures; ?></b>. You can enable it via php.ini or contact your hosting provider to enable this extension.
         </div>
     </div>
   <?php
        }
    }
}

/**
 * Notice for Cloudflare rocket loader usage
 * The application wont work good if cloudflare rocket loader is enabled
 * @return null
 */
function _maybe_using_cloudflare_rocket_loader()
{
    $CI = &get_instance();
    $header = $CI->input->get_request_header('Cf-Ray');

    if ($header && !empty($header) && get_option('show_cloudflare_notice') == '1' && is_admin()) {
        ob_start(); ?>
            <div class="col-md-12">
            <div class="alert alert-warning font-medium">
            <div class="mtop15"></div>
            <h4><strong>Cloudflare usage detected</strong></h4><hr />
            <ul>
                <li>When using Cloudflare with the application <strong>you must disable ROCKET LOADER</strong> feature from Cloudflare options in order everything to work properly. <br /><strong><small>NOTE: The script can't check if Rocket Loader is enabled/disabled in your Cloudflare account. If Rocket Loader is already disabled you can ignore this warning.</small></strong></li>
            <li>
            <br />
                <ul>
                    <li><strong>&nbsp;&nbsp;- Disable Rocket Loader for whole domain name</strong></li>
                    <li>&nbsp;&nbsp;&nbsp;&nbsp;Login to your Cloudflare account and click on the <strong>Speed</strong> tab from the top dashboard, search for Rocket Loader and <strong>set to Off</strong>.</li>
                    <br />
                    <li><strong>&nbsp;&nbsp;- Disable Rocket Loader with page rule for application installation url</strong></li>
                    <li>
                        &nbsp;&nbsp;&nbsp;&nbsp;If you do not want to turn off Rocket Loader for the whole domain you can add <a href="https://support.cloudflare.com/hc/en-us/articles/200168306-Is-there-a-tutorial-for-Page-Rules-" target="_blank">page rule</a> that will disable the Rocket Loader only for the application, follow the steps below in order to achieve this.
                        <br /><br />
                        <p class="no-margin">&nbsp;&nbsp;- Login to your Cloudflare account and click on the <strong>Page Rules</strong> tab from the top dashboard</p>
                        <p class="no-margin">&nbsp;&nbsp;- Click on <strong>Create Page Rule</strong></p>
                        <p class="no-margin">&nbsp;&nbsp;- In the url field add the following url: <strong><?php echo rtrim(site_url(), '/').'/'; ?>*</strong></p>
                        <p class="no-margin">&nbsp;&nbsp;- Click <strong>Add Setting</strong> and search for <strong>Rocket Loader</strong></p>
                        <p class="no-margin">&nbsp;&nbsp;- After you select Rocket Loader <strong>set value to Off</strong></p>
                        <p class="no-margin">&nbsp;&nbsp;- Click <strong>Save and Deploy</strong></p>
                    </li>
                </ul>
            </li>
            </ul>
            <br /><br /><a href="<?php echo admin_url('misc/dismiss_cloudflare_notice'); ?>" class="alert-link">Got it! Don't show this message again</a>&nbsp;&nbsp;&nbsp;&nbsp;<a href="<?php echo admin_url('misc/dismiss_cloudflare_notice'); ?>" class="alert-link">Rocket loader is already disabled</a>
            </div>
            </div>
    <?php
    $contents = ob_get_contents();
        ob_end_clean();
        echo $contents;
    }
}

/**
 * Check few timezones statements
 * @return void
 */
function _maybe_timezone_not_set()
{
    if (get_option('default_timezone') == '') {
        echo '<div class="col-md-12">';
        echo '<div class="alert alert-danger">';
        echo '<strong>Default timezone not set. Navigate to Setup->Settings->Localization to set default system timezone.</strong>';
        echo '</div>';
        echo '</div>';
    } else {
        if (!in_array(get_option('default_timezone'), array_flatten(get_timezones_list()))) {
            echo '<div class="col-md-12">';
            echo '<div class="alert alert-danger">';
            echo '<strong>We updated the timezone logic for the app. Seems like your previous timezone do not fit with the new logic. Navigate to Setup->Settings->Localization to set new proper timezone.</strong>';
            echo '</div>';
            echo '</div>';
        }
    }
}

/**
 * Check if there is usage of some features that requires cron job to be setup
 * If the script found results will output a message inside the admin area only for admins
 * @return void
 */
function _is_cron_setup_required()
{
    if (get_option('cron_has_run_from_cli') == 0) {
        if (is_admin()) {
            $used_features       = array();
            $using_cron_features = 0;
            $feature             = total_rows('tblreminders');
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Reminders');
            }
            $feature = get_option('auto_backup_enabled');
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Auto database backup');
            }

            $feature = get_option('email_queue_enabled');
            $using_cron_features += $feature;
            if ($feature == 1) {
                array_push($used_features, 'Email Queue');
            }


            $feature = total_rows('tblsurveysemailsendcron');
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Surveys');
            }
            $feature = total_rows('tblleadsintegration', array(
                'active' => 1
            ));
            $using_cron_features += $feature;

            if ($feature > 0) {
                array_push($used_features, 'Auto importing leads from email.');
            }
            $feature = total_rows('tblinvoices', array(
                'recurring >' => 0
            ));
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Recurring Invoices');
            }
            $feature = total_rows('tblexpenses', array(
                'recurring' => 1
            ));
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Recurring Expenses');
            }

            $feature = total_rows('tblstafftasks', array(
                'recurring' => 1
            ));
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Recurring Tasks');
            }

            $feature = total_rows('tblevents');
            $using_cron_features += $feature;

            if ($feature > 0) {
                array_push($used_features, 'Custom Calendar Events');
            }

            $feature = total_rows('tbldepartments', array(
                'host !=' => '',
                'password !=' => '',
                'email !=' => ''
            ));
            $using_cron_features += $feature;
            if ($feature > 0) {
                array_push($used_features, 'Auto Import Tickets via method IMAP (Setup->Support->Departments)');
            }

            if ($using_cron_features > 0 && get_option('hide_cron_is_required_message') == 0) {
                echo '<div class="col-md-12">';
                echo '<div class="alert alert-danger">';
                echo 'You are using some features that requires cron job setup to work properly.';
                echo '<br />Please follow the cron <a href="https://help.perfexcrm.com/setup-cron-job/" target="_blank">setup guide</a> in order all features to work well.';
                echo '<br /><br /><br />';
                echo '<p class="bold">You are using the following features that CRON Job setup is required:</p>';
                $i = 1;
                foreach ($used_features as $feature) {
                    echo '&nbsp;' . $i . '. ' . $feature . '<br />';
                    $i++;
                }
                echo '<br /><br /><a href="' . admin_url('misc/dismiss_cron_setup_message') . '" class="alert-link">Don\'t show this message again</a>';
                echo '</div>';
                echo '</div>';
            }
        }
    }
}

/**
 * Show message on dashboard when environment is set to development or testing
 * @return void
 */
function _show_development_mode_message()
{
    if (ENVIRONMENT == 'development' || ENVIRONMENT == 'testing') {
        if (is_admin()) {
            echo '<div class="col-md-12">';
            echo '<div class="alert alert-warning">';
            echo 'Environment set to <b>' . ENVIRONMENT . '</b>. Don\'t forget to set back to <b>production</b> in the main index.php file after finishing your tests.';
            echo '</div>';
            echo '</div>';
        }
    }
}

/**
 * On each update there is message/code inserted in the database
 */
function _show_just_updated_message()
{
    if (get_option('update_info_message') != '') {
        if (is_admin()) {
            $message = get_option('update_info_message');
            update_option('update_info_message', '');
            echo $message;
        }
    }
}

function _system_popup_message_ticket_form($ticket_id)
{
    if ($ticket_id == 1) {
        set_system_popup('First Ticket Created! <br /> <span style="font-size:26px;">Did you know that you can embed Ticket Form (Setup->Settings->Support->Ticket Form) directly in your websites?</span>');
    }
}

function _system_popup_message_web_to_lead_form($lead_id)
{
    if ($lead_id ==1) {
        set_system_popup('First Leads Created! <br /> <span style="font-size:26px;">You can use Web To Lead Forms (Setup->Leads->Web To Lead) to capture leads directly from your website.</span>');
    }
}
function _system_popup_message_tags_styling($tag_id)
{
    if ($tag_id == 1) {
        set_system_popup('Congrats! You created the first tags! <br /> Did you know that you can apply color to tags in Setup->Theme Style?');
    }
}

function _system_popup_message_email_configured()
{
    if (get_option('smtp_email') != '' && get_option('email_protocol') == 'smtp' && get_option('smtp_host') != '') {
        if (get_option('_smtp_test_email_success') === '') {
            set_system_popup('Congrats! You configured the email feature successfully! <br /> <span style="font-size:26px;">You can disable any emails that you don\'t want to be sent in Setup->Email Templates.</span>');
            add_option('_smtp_test_email_success', 1, 0);
        }
    }
}

function show_pdf_unable_to_get_image_size_error()
{
    ?>
   <div style="font-size:17px;">
   <hr />
    <p>This error can be shown if the <b>PDF library can't read the image from your server</b>.</p>
    <p>Very often this is happening <b>when you are using custom PDF logo url in Setup -> Settings -> PDF</b>, first make sure that the url you added in Setup->Settings->PDF for the custom pdf logo is valid and the image exists if the problem still exists you will need to use a <b>direct path</b> to the image to include in the PDF documents. Follow the steps mentioned below:</p>
    <p><strong>Method 1 (easy)</strong></p>
    <ul>
        <li>Upload the logo image in the installation directory eq. <?php echo FCPATH; ?>mylogo.jpg</li>
        <li><a href="<?php echo admin_url('settings?group=pdf'); ?>" target="_blank">Navigate to Setup -> Settings -> PDF</a> -> Custom PDF Company Logo URL and only add the filename like: <b>mylogo.jpg</b>, now Custom PDF Company Logo URL should be only filename not full URL.</li>
        <li>Try to re-generate PDF document again.</li>
    </ul>
     <p><strong>Method 2 (advanced)</strong></p>
     <small>Try this method if method 1 is still not working.</small>
    <ul>
        <li>Consult with your hosting provider to confirm that the server is able to use PHP's <a href="http://php.net/manual/en/function.file-get-contents.php" target="_blank">file_get_contents</a> or <a href="http://php.net/manual/en/curl.examples-basic.php" target="_blank">cUrl</a> to download the file. </li>
        <li>Try to re-generate PDF document again.</li>
    </ul>
   </div>
<?php
}


function _system_popup_message_timers_with_no_task($data)
{
    $task_id = $data['task_id'];
    $timer_id = $data['timer_id'];
    if ($task_id != '0' && $timer_id == 1) {
        set_system_popup('First Timer Started!<br />
        <span style="font-size:26px;">Did you know that you can start a timer without task and assign the timer to task afterward?</span><br /><br /><img alt="timer-start" class="img-responsive center-block" src="data:image/png;base64,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">');
    }
}


function _system_popup_task_checklist_items_drag_ability($data)
{
    $item_id = $data['checklist_id'];
    $task_id = $data['task_id'];
    if (($task_id == 1 || $task_id == 2 || $task_id == 3 || $task_id == 4 || $task_id == 5) && $item_id == 8) {
        set_system_popup('Seems like that you are enjoying creating tasks checklist items, did you know that you can easily re-order the items by dragging them above or below? <br /><br />
           <img alt="checklist-items-drag" class="img-responsive center-block" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAAClCAYAAACqcw9sAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyZpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMDY3IDc5LjE1Nzc0NywgMjAxNS8wMy8zMC0yMzo0MDo0MiAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIDIwMTUgKFdpbmRvd3MpIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjI3ODFDQjEyQkNCMzExRTdBQTA1OEI3NTE4MzhCRDlDIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjI3ODFDQjEzQkNCMzExRTdBQTA1OEI3NTE4MzhCRDlDIj4gPHhtcE1NOkRlcml2ZWRGcm9tIHN0UmVmOmluc3RhbmNlSUQ9InhtcC5paWQ6Mjc4MUNCMTBCQ0IzMTFFN0FBMDU4Qjc1MTgzOEJEOUMiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6Mjc4MUNCMTFCQ0IzMTFFN0FBMDU4Qjc1MTgzOEJEOUMiLz4gPC9yZGY6RGVzY3JpcHRpb24+IDwvcmRmOlJERj4gPC94OnhtcG1ldGE+IDw/eHBhY2tldCBlbmQ9InIiPz51tX63AAAeSklEQVR42uzd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">');
    }
}
