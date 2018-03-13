<?php
defined('BASEPATH') or exit('No direct script access allowed');

add_action('settings_groups', '_maybe_sms_gateways_settings_group');

function _maybe_sms_gateways_settings_group($groups)
{
    $CI = &get_instance();
    $gateways = $CI->sms->get_gateways();
    if (count($gateways) > 0) {
        $groups[] = array(
          'name'=>'sms',
          'lang'=>'SMS',
          'order'=>12,
          );
    }

    return $groups;
}

add_action('app_init', '_init_core_sms_gateways');

function _init_core_sms_gateways()
{
    $CI = &get_instance();

    $CI->sms->add_gateway('twilio', array(
            'name'=>'Twilio',
            'info'=>'<p>Twilio SMS integration is one way messaging, means that your customers won\'t be able to reply to the SMS. Phone numbers must be in format <a href="https://www.twilio.com/docs/glossary/what-e164" target="_blank">E.164</a>. Click <a href="https://support.twilio.com/hc/en-us/articles/223183008-Formatting-International-Phone-Numbers" target="_blank">here</a> to read more how phone numbers should be formatted.</p><hr class="hr-10" />',
            'options'=> array(
                array(
                    'name'=>'account_sid',
                    'label'=>'Account SID',
                ),
                array(
                    'name'=>'auth_token',
                    'label'=>'Auth Token',
                ),
                array(
                    'name'=>"phone_number",
                    'label'=>'Twilio Phone Number',
                ),
            ),
        ));

    $CI->sms->add_gateway('clickatell', array(
            'info'=>"<p>Clickatell SMS integration is one way messaging, means that your customers won't be able to reply to the SMS.</p><hr class='hr-10'>",
            'name'=>'Clickatell',
            'options'=> array(
                array(
                    'name'=>'api_key',
                    'label'=>'API Key',
                ),
            ),
        ));
}

function is_sms_trigger_active($trigger = '')
{
    $CI = &get_instance();
    $active = $CI->sms->get_activate_gateway();

    if (!$active) {
        return false;
    }

    return $CI->sms->is_trigger_active($trigger);
}

function can_send_sms_based_on_creation_date($data_date_created)
{
    $now = time();
    $your_date = strtotime($data_date_created);
    $datediff = $now - $your_date;

    $days_diff = floor($datediff / (60 * 60 * 24));

    return $days_diff < DO_NOT_SEND_SMS_ON_DATA_OLDER_THEN || $days_diff == DO_NOT_SEND_SMS_ON_DATA_OLDER_THEN;
}

function twilio_trigger_send_sms($number, $message)
{
    $CI = &get_instance();

    // Using composer
    // require_once(APPPATH . '/third_party/twilio/Twilio/autoload.php');

    // Account SID from twilio.com/console
    static $sid;
    // Auth Token from twilio.com/console
    static $token;
    // Twilio Phone Number
    static $phone;

    if (!$sid) {
        $sid = $CI->sms->get_option('twilio', 'account_sid');
    }

    if (!$token) {
        $token = $CI->sms->get_option('twilio', 'auth_token');
    }

    if (!$phone) {
        $phone = $CI->sms->get_option('twilio', 'phone_number');
    }

    $client = new Twilio\Rest\Client($sid, $token);

    try {
        $client->messages->create(
                // The number to send the SMS
                $number,
                array(
                     // A Twilio phone number you purchased at twilio.com/console
                    'from' => $phone,
                    'body' => $message,
                )
            );
        logActivity('SMS to send via Twilio to '.$number.', Message: '.$message);
    } catch (Exception $e) {
        logActivity('Failed to send SMS via Twilio: '.$e->getMessage());

        return false;
    }

    return true;
}

function clickatell_trigger_send_sms($number, $message)
{
    $CI = &get_instance();

    static $api_key;

    if (!$api_key) {
        $api_key = $CI->sms->get_option('clickatell', 'api_key');
    }

    // No from number, in clickatell from is used only in 2 way messaging
    $url = 'https://platform.clickatell.com/messages/http/send?' . http_build_query(array(
      'apiKey' =>  $api_key,
      'to' => $number,
      'content' => $message,
    ));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-Version: 1",
        "Content-Type: application/json",
        "Accept: application/json",
    ));

    $result = curl_exec($ch);

    $error = false;

    if (!$result) {
        $error = curl_error($ch);
    } elseif ($result) {
        $result = json_decode($result);

        if (isset($result->messages[0]->accepted) && $result->messages[0]->accepted == true) {
            logActivity('SMS to send via Clickatell to '.$number.', Message: '.$message);
            return true;
        } elseif (isset($result->messages) && isset($result->error)) {
            $error = $result->error;
        } elseif (isset($result->messages[0]->error) && $result->messages[0]->error != null) {
            $error = $result->messages[0]->error;
        }
    }

    if ($error !== false && $error !== null) {
        logActivity('Failed to send SMS via Clickatell: '.$error);
    }

    return false;
}
