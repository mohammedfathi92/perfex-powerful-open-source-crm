#!/usr/local/bin/php
<?php

error_reporting(1);

$environment = 'development';

$system_path = dirname(__FILE__) . DIRECTORY_SEPARATOR .'system';

$application_folder = dirname(__FILE__) . DIRECTORY_SEPARATOR .'application';

if (realpath($system_path) !== false) {
    $system_path = realpath($system_path) . '/';
}

$system_path = rtrim($system_path, '/') . '/';

define('BASEPATH', str_replace("\\", "/", $system_path));
define('APPPATH', $application_folder . '/');
define('EXT', '.php');
define('ENVIRONMENT', $environment ? $environment : 'development');

require(BASEPATH .'core/Common.php');

if (file_exists(APPPATH.'config/'.ENVIRONMENT.'/constants.php')) {
    require(APPPATH.'config/'.ENVIRONMENT.'/constants.php');
} else {
    require(APPPATH.'config/constants.php');
}

define('FCPATH', dirname(__FILE__).'/');

$GLOBALS['CFG'] =& load_class('Config', 'core');
$GLOBALS['UNI'] =& load_class('Utf8', 'core');

if (file_exists($basepath.'core/Security.php')) {
  $GLOBALS['SEC'] =& load_class('Security', 'core');
}

load_class('Loader', 'core');
load_class('Router', 'core');
load_class('Input', 'core');
load_class('Lang', 'core');

require(BASEPATH . 'core/Controller.php');

function &get_instance() {
    return CI_Controller::get_instance();
}

$class = 'CI_Controller';
$instance = new $class();
$email_output = array();

$fd = fopen("php://stdin", "r");
$input = "";
while( !feof($fd) )
{
    $input .= fread($fd, 1024);
}
fclose($fd);
require_once(APPPATH . 'third_party/Mime_decode.php');

$decode_params["input"]          = $input;
$decode_params["include_bodies"] = true;
$decode_params["decode_bodies"]  = true;
$decode_params["decode_headers"] = true;

$decode                        = new Mail_mimeDecode($input);
$structure                     = $decode->decode($decode_params);
$email_output['headers'] = $structure->headers;

interpret_structure($structure);
if ($email_output["body"]["text/plain"]) {
    $body = $email_output["body"]["text/plain"];
} else {
    if ($email_output["body"]["text/html"]) {
        $body = strip_tags($email_output["body"]["text/html"]);
    } else {
        $body = "No message found.";
    }
}

$from        = $email_output["headers"]["from"];
$to          = $email_output["headers"]["to"];
$cc          = $email_output["headers"]["cc"];
$bcc         = $email_output["headers"]["bcc"];
if (!$to) {
    $to = $email_output["headers"]["resent-to"];
}

$subject  = $email_output["headers"]["subject"];

$fromname = preg_replace("/(.*)<(.*)>/", "\\1", $from);
$fromname = str_replace("\"", "", $fromname);
if ($email_output["headers"]["reply-to"]) {
    $fromemail = $email_output["headers"]["reply-to"];
}

$fromemail = preg_replace("/(.*)<(.*)>/", "\\2", $from);
$to        = explode(",", $to);
foreach ($to as $toemail) {
    if (strpos("." . $toemail, "<")) {
        $toemails[] = preg_replace("/(.*)<(.*)>/", "\\2", $toemail);
    } else {
        $toemails[] = $toemail;
    }

}
$to = explode(",", $cc);
foreach ($to as $toemail) {
    if (strpos("." . $toemail, "<")) {
        $toemails[] = preg_replace("/(.*)<(.*)>/", "\\2", $toemail);
    } else {
        $toemails[] = $toemail;
    }

}
$to = explode(",", $bcc);
foreach ($to as $toemail) {
    if (strpos("." . $toemail, "<")) {
        $toemails[] = preg_replace("/(.*)<(.*)>/", "\\2", $toemail);
    } else {
        $toemails[] = $toemail;
    }
}

$to = implode(",", $toemails);
$instance->load->model('tickets_model');

$pattern = '#\bhttps?://drive.google.com[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#';

preg_match_all($pattern, $body, $matchGoogleDriveLinks);
    if(isset($matchGoogleDriveLinks[0]) && is_array($matchGoogleDriveLinks[0])){
    foreach($matchGoogleDriveLinks[0] as $driveLink){
        $link = '<a href="'.$driveLink.'">'.$driveLink.'</a>';
        $body = str_replace($driveLink, $link,$body);
        $body = str_replace('<'.$link.'>', $link ,$body);
    }
}

$instance->tickets_model->insert_piped_ticket(array(
    'to'=>$to,
    'fromname'=>$fromname,
    'email'=>$fromemail,
    'subject'=>$subject,
    'body'=>$body,
    'attachments'=>$email_output["attachments"],
    ));

function interpret_structure($structure)
{
    global $email_output;
    $ctype = strtolower($structure->ctype_primary) . "/" . strtolower($structure->ctype_secondary);
    if (!$ctype) {
        $ctype = "text/plain";
    }

    if ($ctype == "text/html" || $ctype == "text/plain") {
        $charset = "us-ascii";
        if (!empty($structure->ctype_parameters) && isset($structure->ctype_parameters["charset"])) {
            $charset = $structure->ctype_parameters["charset"];
        }

        if (!empty($structure->disposition) && $structure->disposition == "attachment") {
            handle_attachment($structure);
        } else {
            $var      = $ctype == "text/html" ? "html" : "text";
            $bodyUtf8 = $structure->body;
            if ($charset == "UTF-8") {
                $charset = "";
            }

            if ($charset && function_exists("iconv")) {
                $bodyUtf8 = iconv($charset, "utf-8", $bodyUtf8);
                if (isset($email_output["headers"]["convertedcharset"])) {
                    $email_output["headers"]["subject"]          = iconv($charset, "utf-8", $email_output["headers"]["subject"]);
                    $email_output["headers"]["convertedcharset"] = true;
                }

            }

            $email_output["body"][$ctype] = trim($bodyUtf8);
        }

    } else {
        if (strtolower($structure->ctype_primary) == "multipart") {
            if (!empty($structure->parts)) {
                for ($i = 0; $i < count($structure->parts); $i++) {
                    interpret_structure($structure->parts[$i]);
                }
            }

        } else {
            handle_attachment($structure);
        }
    }
}

function handle_attachment($structure)
{
    global $email_output;
    if (!empty($structure->d_parameters["filename"])) {
        $filename = $structure->d_parameters["filename"];
    } else {
        if (!empty($structure->ctype_parameters["name"])) {
            $filename = $structure->ctype_parameters["name"];
        } else {
            return NULL;
        }
    }

    $ctype                               = strtolower($structure->ctype_primary) . "/" . strtolower($structure->ctype_secondary);
    $email_output["attachments"][] = array(
        "data" => $structure->body,
        "size" => strlen($structure->body),
        "filename" => $filename,
        "contenttype" => $ctype
        );
}
