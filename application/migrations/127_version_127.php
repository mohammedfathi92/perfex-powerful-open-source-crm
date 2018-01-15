<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Version_127 extends CI_Migration
{
    function __construct()
    {
        parent::__construct();
    }

    public function up()
    {
        $this->db->query("ALTER TABLE  `tblemailtemplates` CHANGE  `plaintext`  `plaintext` INT( 11 ) NOT NULL DEFAULT  '0';");

        if ($this->session->has_userdata('update_encryption_key')) {
            $enc = $this->session->userdata('update_encryption_key');
        } else {
            $enc = $this->config->item('encryption_key');
        }
        $base = $this->config->item('base_url');

        $db_name  = $this->db->database;
        $hostname = $this->db->hostname;
        $username = $this->db->username;
        $password = $this->db->password;
        $sess_driver = $this->config->item('sess_driver');
        $sess_save_path = $this->config->item('sess_save_path');

        $new_config_file = '<?php defined(\'BASEPATH\') OR exit(\'No direct script access allowed\');
/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|   http://example.com/
|
| If this is not set tshen CodeIgniter will try guess the protocol, domain
| and path to your installation. However, you should always configure this
| explicitly and nevessr rely on auto-guessing, especially in production
| environments.
|
*/

define(\'APP_BASE_URL\',\'' . $base . '\');

/*
|--------------------------------------------------------------------------
| Encryption Key
| IMPORTANT: Dont change this EVER
|--------------------------------------------------------------------------
|
| If you use the Encryption class, you must set an encryption key.
| See the user guide for more info.
|
| http://codeigniter.com/user_guide/libraries/encryption.html
|
*/

define(\'APP_ENC_KEY\',\'' . $enc . '\');

/* Database credentials */

/* The hostname of your database server. */
define(\'APP_DB_HOSTNAME\',\'' . $hostname . '\');
/* The username used to connect to the database */
define(\'APP_DB_USERNAME\',\'' . $username . '\');
/* The password used to connect to the database */
define(\'APP_DB_PASSWORD\',\'' . $password . '\');
/* The name of the database you want to connect to */
define(\'APP_DB_NAME\',\'' . $db_name . '\');

/* Session Handler */

define(\'SESS_DRIVER\',\'' . $sess_driver . '\');
define(\'SESS_SAVE_PATH\',\'' . $sess_save_path . '\');';

        $fp = fopen(APPPATH . 'config/app-config.php', 'w');
        if ($fp) {
            fwrite($fp, $new_config_file);
            fclose($fp);

            $fp = fopen(APPPATH . 'config/config.php', 'w+');
            if ($fp) {
                $update_old_config = '<?php defined(\'BASEPATH\') OR exit(\'No direct script access allowed\');

spl_autoload_register(function ($class) {
    if(strpos($class, \'CRM_\') !== 0)
    {
        @include_once( APPPATH . \'core/\'. $class . \'.php\' );
    }
});

if(file_exists(APPPATH.\'config/app-config.php\')){
    include_once(APPPATH.\'config/app-config.php\');
} else {
    echo \'<h1>Rename the config file located in application/config/app-config-sample.php to app-config.php</h1>\';
    die;
}

/*
|--------------------------------------------------------------------------
| Base Site URL
|--------------------------------------------------------------------------
|
| URL to your CodeIgniter root. Typically this will be your base URL,
| WITH a trailing slash:
|
|   http://example.com/
|
| If this is not set tshen CodeIgniter will try guess the protocol, domain
| and path to your installation. However, you should always configure this
| explicitly and nevessr rely on auto-guessing, especially in production
| environments.
|
*/

$config[\'base_url\'] = APP_BASE_URL;

/*
|--------------------------------------------------------------------------
| Index File
|--------------------------------------------------------------------------
|
| Typically this will be your index.php file, unless you\'ve renamed it to
| something else. If you are using mod_rewrite to remove the page set this
| variable so that it is blank.
|
*/
$config[\'index_page\'] = \'\';

/*
|--------------------------------------------------------------------------
| URI PROTOCOL
|--------------------------------------------------------------------------
|
| This item determines which server global should be used to retrieve the
| URI string.  The default setting of \'REQUEST_URI\' works for most servers.
| If your links do not seem to work, try one of the other delicious flavors:
|
| \'REQUEST_URI\'    Uses $_SERVER[\'REQUEST_URI\']
| \'QUERY_STRING\'   Uses $_SERVER[\'QUERY_STRING\']
| \'PATH_INFO\'      Uses $_SERVER[\'PATH_INFO\']
|
| WARNING: If you set this to \'PATH_INFO\', URIs will always be URL-decoded!
*/
$config[\'uri_protocol\'] = \'AUTO\';

/*
|--------------------------------------------------------------------------
| URL suffix
|--------------------------------------------------------------------------
|
| This option allows you to add a suffix to all URLs generated by CodeIgniter.
| For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/urls.html
*/
$config[\'url_suffix\'] = \'\';

/*
|--------------------------------------------------------------------------
| Default Language
|--------------------------------------------------------------------------
|
| This determines which set of language files should be used. Make sure
| there is an available translation if you intend to use something other
| than english.
|
*/
$config[\'language\'] = \'\';

/*
|--------------------------------------------------------------------------
| Default Character Set
|--------------------------------------------------------------------------
|
| This determines which character set is used by default in various methods
| that require a character set to be provided.
|
| See http://php.net/htmlspecialchars for a list of supported charsets.
|
*/
$config[\'charset\'] = \'UTF-8\';

/*
|--------------------------------------------------------------------------
| Enable/Disable System Hooks
|--------------------------------------------------------------------------
|
| If you would like to use the \'hooks\' feature you must enable it by
| setting this variable to TRUE (boolean).  See the user guide for details.
|
*/
$config[\'enable_hooks\'] = FALSE;

/*
|--------------------------------------------------------------------------
| Class Extension Prefix
|--------------------------------------------------------------------------
|
| This item allows you to set the filename/classname prefix when extending
| native libraries.  For more information please see the user guide:
|
| http://codeigniter.com/user_guide/general/core_classes.html
| http://codeigniter.com/user_guide/general/creating_libraries.html
|
*/
$config[\'subclass_prefix\'] = \'CRM_\';

/*
|--------------------------------------------------------------------------
| Composer auto-loading
|--------------------------------------------------------------------------
|
| Enabling this setting will tell CodeIgniter to look for a Composer
| package auto-loader script in application/vendor/autoload.php.
|
|   $config[\'composer_autoload\'] = TRUE;
|
| Or if you have your vendor/ directory located somewhere else, you
| can opt to set a specific path as well:
|
|   $config[\'composer_autoload\'] = \'/path/to/vendor/autoload.php\';
|
| For more information about Composer, please visit http://getcomposer.org/
|
| Note: This will NOT disable or override the CodeIgniter-specific
|   autoloading (application/config/autoload.php)
*/
$config[\'composer_autoload\'] = FALSE;

/*
|--------------------------------------------------------------------------
| Allowed URL Characters
|--------------------------------------------------------------------------
|
| This lets you specify which characters are permitted within your URLs.
| When someone tries to submit a URL with disallowed characters they will
| get a warning message.
|
| As a security measure you are STRONGLY encouraged to restrict URLs to
| as few characters as possible.  By default only these are allowed: a-z 0-9~%.:_-
|
| Leave blank to allow all characters -- but only if you are insane.
|
| The configured value is actually a regular expression character group
| and it will be executed as: ! preg_match(\'/^[<permitted_uri_chars>]+$/i
|
| DO NOT CHANGE THIS UNLESS YOU FULLY UNDERSTAND THE REPERCUSSIONS!!
|
*/
$config[\'permitted_uri_chars\'] = \'a-z 0-9~%.:_\-@\';

/*
|--------------------------------------------------------------------------
| Enable Query Strings
|--------------------------------------------------------------------------
|
| By default CodeIgniter uses search-engine friendly segment based URLs:
| example.com/who/what/where/
|
| By default CodeIgniter enables access to the $_GET array.  If for some
| reason you would like to disable it, set \'allow_get_array\' to FALSE.
|
| You can optionally enable standard query string based URLs:
| example.com?who=me&what=something&where=here
|
| Options are: TRUE or FALSE (boolean)
|
| The other items let you set the query string \'words\' that will
| invoke your controllers and its functions:
| example.com/index.php?c=controller&m=function
|
| Please note that some of the helpers won\'t work as expected when
| this feature is enabled, since CodeIgniter is designed primarily to
| use segment based URLs.
|
*/
$config[\'allow_get_array\'] = TRUE;
$config[\'enable_query_strings\'] = FALSE;
$config[\'controller_trigger\'] = \'c\';
$config[\'function_trigger\'] = \'m\';
$config[\'directory_trigger\'] = \'d\';

/*
|--------------------------------------------------------------------------
| Error Logging Threshold
|--------------------------------------------------------------------------
|
| You can enable error logging by setting a threshold over zero. The
| threshold determines what gets logged. Threshold options are:
|
|   0 = Disables logging, Error logging TURNED OFF
|   1 = Error Messages (including PHP errors)
|   2 = Debug Messages
|   3 = Informational Messages
|   4 = All Messages
|
| You can also pass an array with threshold levels to show individual error types
|
|   array(2) = Debug Messages, without Error Messages
|
| For a live site you\'ll usually only enable Errors (1) to be logged otherwise
| your log files will fill up very fast.
|
*/
$config[\'log_threshold\'] = (ENVIRONMENT !== \'production\' ? 1 : 0);

/*
|--------------------------------------------------------------------------
| Error Logging Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/logs/ directory. Use a full server path with trailing slash.
|
*/
$config[\'log_path\'] = \'\';

/*
|--------------------------------------------------------------------------
| Log File Extension
|--------------------------------------------------------------------------
|
| The default filename extension for log files. The default \'php\' allows for
| protecting the log files via basic scripting, when they are to be stored
| under a publicly accessible directory.
|
| Note: Leaving it blank will default to \'php\'.
|
*/
$config[\'log_file_extension\'] = \'\';

/*
|--------------------------------------------------------------------------
| Log File Permissions
|--------------------------------------------------------------------------
|
| The file system permissions to be applied on newly created log files.
|
| IMPORTANT: This MUST be an integer (no quotes) and you MUST use octal
|            integer notation (i.e. 0700, 0644, etc.)
*/
$config[\'log_file_permissions\'] = 0644;

/*
|--------------------------------------------------------------------------
| Date Format for Logs
|--------------------------------------------------------------------------
|
| Each item that is logged has an associated date. You can use PHP date
| codes to set your own date formatting
|
*/
$config[\'log_date_format\'] = \'Y-m-d H:i:s\';

/*
|--------------------------------------------------------------------------
| Error Views Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/views/errors/ directory.  Use a full server path with trailing slash.
|
*/
$config[\'error_views_path\'] = \'\';

/*
|--------------------------------------------------------------------------
| Cache Directory Path
|--------------------------------------------------------------------------
|
| Leave this BLANK unless you would like to set something other than the default
| application/cache/ directory.  Use a full server path with trailing slash.
|
*/
$config[\'cache_path\'] = \'\';

/*
|--------------------------------------------------------------------------
| Cache Include Query String
|--------------------------------------------------------------------------
|
| Whether to take the URL query string into consideration when generating
| output cache files. Valid options are:
|
|   FALSE      = Disabled
|   TRUE       = Enabled, take all query parameters into account.
|                Please be aware that this may result in numerous cache
|                files generated for the same page over and over again.
|   array(\'q\') = Enabled, but only take into account the specified list
|                of query parameters.
|
*/
$config[\'cache_query_string\'] = FALSE;

/*IMPORTANT: Dont change this EVER !!
|--------------------------------------------------------------------------
| Encryption Key
|--------------------------------------------------------------------------
|
| If you use the Encryption class, you must set an encryption key.
| See the user guide for more info.
|
| http://codeigniter.com/user_guide/libraries/encryption.html
|
*/
$config[\'encryption_key\'] = APP_ENC_KEY;

/*
|--------------------------------------------------------------------------
| Session Variables
|--------------------------------------------------------------------------
|
| \'sess_driver\'
|
|   The storage driver to use: files, database, redis, memcached
|
| \'sess_cookie_name\'
|
|   The session cookie name, must contain only [0-9a-z_-] characters
|
| \'sess_expiration\'
|
|   The number of SECONDS you want the session to last.
|   Setting to 0 (zero) means expire when the browser is closed.
|
| \'sess_save_path\'
|
|   The location to save sessions to, driver dependent.
|
|   For the \'files\' driver, it\'s a path to a writable directory.
|   WARNING: Only absolute paths are supported!
|
|   For the \'database\' driver, it\'s a table name.
|   Please read up the manual for the format with other session drivers.
|
|   IMPORTANT: You are REQUIRED to set a valid save path!
|
| \'sess_match_ip\'
|
|   Whether to match the user\'s IP address when reading the session data.
|
|   WARNING: If you\'re using the database driver, don\'t forget to update
|            your session table\'s PRIMARY KEY when changing this setting.
|
| \'sess_time_to_update\'
|
|   How many seconds between CI regenerating the session ID.
|
| \'sess_regenerate_destroy\'
|
|   Whether to destroy session data associated with the old session ID
|   when auto-regenerating the session ID. When set to FALSE, the data
|   will be later deleted by the garbage collector.
|
| Other session cookie settings are shared with the rest of the application,
| except for \'cookie_prefix\' and \'cookie_httponly\', which are ignored here.
|
*/
$config[\'sess_driver\'] = SESS_DRIVER;
$config[\'sess_cookie_name\'] = \'sp_session\';
$config[\'sess_expiration\'] = 28800;
$config[\'sess_save_path\'] = SESS_SAVE_PATH;
$config[\'sess_match_ip\'] = FALSE;
$config[\'sess_time_to_update\'] = 300;
$config[\'sess_regenerate_destroy\'] = FALSE;

/*
|--------------------------------------------------------------------------
| Cookie Related Variables
|--------------------------------------------------------------------------
|
| \'cookie_prefix\'   = Set a cookie name prefix if you need to avoid collisions
| \'cookie_domain\'   = Set to .your-domain.com for site-wide cookies
| \'cookie_path\'     = Typically will be a forward slash
| \'cookie_secure\'   = Cookie will only be set if a secure HTTPS connection exists.
| \'cookie_httponly\' = Cookie will only be accessible via HTTP(S) (no javascript)
|
| Note: These settings (with the exception of \'cookie_prefix\' and
|       \'cookie_httponly\') will also affect sessions.
|
*/
$config[\'cookie_prefix\']    = \'\';
$config[\'cookie_domain\']    = \'\';
$config[\'cookie_path\']      = \'/\';
$config[\'cookie_secure\']    = FALSE;
$config[\'cookie_httponly\']  = FALSE;

/*
|--------------------------------------------------------------------------
| Standardize newlines
|--------------------------------------------------------------------------
|
| Determines whether to standardize newline characters in input data,
| meaning to replace \r\n, \r, \n occurrences with the PHP_EOL value.
|
| This is particularly useful for portability between UNIX-based OSes,
| (usually \n) and Windows (\r\n).
|
*/
$config[\'standardize_newlines\'] = FALSE;

/*
|--------------------------------------------------------------------------
| Global XSS Filtering
|--------------------------------------------------------------------------
|
| Determines whether the XSS filter is always active when GET, POST or
| COOKIE data is encountered
|
| WARNING: This feature is DEPRECATED and currently available only
|          for backwards compatibility purposes!
|
*/
$config[\'global_xss_filtering\'] = TRUE;

/*
|--------------------------------------------------------------------------
| Cross Site Request Forgery
|--------------------------------------------------------------------------
| Enables a CSRF cookie token to be set. When set to TRUE, token will be
| checked on a submitted form. If you are accepting user data, it is strongly
| recommended CSRF protection be enabled.
|
| \'csrf_token_name\' = The token name
| \'csrf_cookie_name\' = The cookie name
| \'csrf_expire\' = The number in seconds the token should expire.
| \'csrf_regenerate\' = Regenerate token on every submission
| \'csrf_exclude_uris\' = Array of URIs which ignore CSRF checks
*/
$config[\'csrf_protection\'] = FALSE;
$config[\'csrf_token_name\'] = \'csrf_test_name\';
$config[\'csrf_cookie_name\'] = \'csrf_cookie_name\';
$config[\'csrf_expire\'] = 7200;
$config[\'csrf_regenerate\'] = TRUE;
$config[\'csrf_exclude_uris\'] = array();

/*
|--------------------------------------------------------------------------
| Output Compression
|--------------------------------------------------------------------------
|
| Enables Gzip output compression for faster page loads.  When enabled,
| the output class will test whether your server supports Gzip.
| Even if it does, however, not all browsers support compression
| so enable only if you are reasonably sure your visitors can handle it.
|
| Only used if zlib.output_compression is turned off in your php.ini.
| Please do not use it together with httpd-level output compression.
|
| VERY IMPORTANT:  If you are getting a blank page when compression is enabled it
| means you are prematurely outputting something to your browser. It could
| even be a line of whitespace at the end of one of your scripts.  For
| compression to work, nothing can be sent before the output buffer is called
| by the output class.  Do not \'echo\' any values with compression enabled.
|
*/
$config[\'compress_output\'] = FALSE;

/*
|--------------------------------------------------------------------------
| Master Time Reference
|--------------------------------------------------------------------------
|
| Options are \'local\' or any PHP supported timezone. This preference tells
| the system whether to use your server\'s local time as the master \'now\'
| reference, or convert it to the configured one timezone. See the \'date
| helper\' page of the user guide for information regarding date handling.
|
*/
$config[\'time_reference\'] = \'local\';

/*
|--------------------------------------------------------------------------
| Rewrite PHP Short Tags
|--------------------------------------------------------------------------
|
| If your PHP installation does not have short tag support enabled CI
| can rewrite the tags on-the-fly, enabling you to utilize that syntax
| in your view files.  Options are TRUE or FALSE (boolean)
|
| Note: You need to have eval() enabled for this to work.
|
*/
$config[\'rewrite_short_tags\'] = FALSE;

/*
|--------------------------------------------------------------------------
| Reverse Proxy IPs
|--------------------------------------------------------------------------
|
| If your server is behind a reverse proxy, you must whitelist the proxy
| IP addresses from which CodeIgniter should trust headers such as
| HTTP_X_FORWARDED_FOR and HTTP_CLIENT_IP in order to properly identify
| the visitor\'s IP address.
|
| You can use both an array or a comma-separated list of proxy addresses,
| as well as specifying whole subnets. Here are a few examples:
|
| Comma-separated:  \'10.0.1.200,192.168.5.0/24\'
| Array:        array(\'10.0.1.200\', \'192.168.5.0/24\')
*/
$config[\'proxy_ips\'] = \'\';


/*
|--------------------------------------------------------------------------
| Custom constant to change the memory limit
|--------------------------------------------------------------------------
|
| APP_MEMORY_LIMIT should be defined in app-config.php file.
*/
if(defined(\'APP_MEMORY_LIMIT\')){
    @ini_set(\'memory_limit\', APP_MEMORY_LIMIT);
}';

                fwrite($fp, $update_old_config);
                fclose($fp);


                $fp = fopen(APPPATH . 'config/database.php', 'w+');
                if ($fp) {
                    $update_old_db_config = '<?php defined(\'BASEPATH\') OR exit(\'No direct script access allowed\');
include_once(APPPATH.\'config/app-config.php\');
/*
| -------------------------------------------------------------------
| DATABASE CONNECTIVITY SETTINGS
| -------------------------------------------------------------------
| This file will contain the settings needed to access your database.
|
| For complete instructions please consult the \'Database Connection\'
| page of the User Guide.
|
| -------------------------------------------------------------------
| EXPLANATION OF VARIABLES
| -------------------------------------------------------------------
|
|   [\'dsn\']      The full DSN string describe a connection to the database.
|   [\'hostname\'] The hostname of your database server.
|   [\'username\'] The username used to connect to the database
|   [\'password\'] The password used to connect to the database
|   [\'database\'] The name of the database you want to connect to
|   [\'dbdriver\'] The database driver. e.g.: mysqli.
|           Currently supported:
|                cubrid, ibase, mssql, mysql, mysqli, oci8,
|                odbc, pdo, postgre, sqlite, sqlite3, sqlsrv
|   [\'dbprefix\'] You can add an optional prefix, which will be added
|                to the table name when using the  Query Builder class
|   [\'pconnect\'] TRUE/FALSE - Whether to use a persistent connection
|   [\'db_debug\'] TRUE/FALSE - Whether database errors should be displayed.
|   [\'cache_on\'] TRUE/FALSE - Enables/disables query caching
|   [\'cachedir\'] The path to the folder where cache files should be stored
|   [\'char_set\'] The character set used in communicating with the database
|   [\'dbcollat\'] The character collation used in communicating with the database
|                NOTE: For MySQL and MySQLi databases, this setting is only used
|                as a backup if your server is running PHP < 5.2.3 or MySQL < 5.0.7
|                (and in table creation queries made with DB Forge).
|                There is an incompatibility in PHP with mysql_real_escape_string() which
|                can make your site vulnerable to SQL injection if you are using a
|                multi-byte character set and are running versions lower than these.
|                Sites using Latin-1 or UTF-8 database character set and collation are unaffected.
|   [\'swap_pre\'] A default table prefix that should be swapped with the dbprefix
|   [\'encrypt\']  Whether or not to use an encrypted connection.
|
|           \'mysql\' (deprecated), \'sqlsrv\' and \'pdo/sqlsrv\' drivers accept TRUE/FALSE
|           \'mysqli\' and \'pdo/mysql\' drivers accept an array with the following options:
|
|               \'ssl_key\'    - Path to the private key file
|               \'ssl_cert\'   - Path to the public key certificate file
|               \'ssl_ca\'     - Path to the certificate authority file
|               \'ssl_capath\' - Path to a directory containing trusted CA certificats in PEM format
|               \'ssl_cipher\' - List of *allowed* ciphers to be used for the encryption, separated by colons (\':\')
|               \'ssl_verify\' - TRUE/FALSE; Whether verify the server certificate or not (\'mysqli\' only)
|
|   [\'compress\'] Whether or not to use client compression (MySQL only)
|   [\'stricton\'] TRUE/FALSE - forces \'Strict Mode\' connections
|                           - good for ensuring strict SQL while developing
|   [\'ssl_options\'] Used to set various SSL options that can be used when making SSL connections.
|   [\'failover\'] array - A array with 0 or more data for connections if the main should fail.
|   [\'save_queries\'] TRUE/FALSE - Whether to "save" all executed queries.
|               NOTE: Disabling this will also effectively disable both
|               $this->db->last_query() and profiling of DB queries.
|               When you run a query, with this setting set to TRUE (default),
|               CodeIgniter will store the SQL statement for debugging purposes.
|               However, this may cause high memory usage, especially if you run
|               a lot of SQL queries ... disable this to avoid that problem.
|
| The $active_group variable lets you choose which connection group to
| make active.  By default there is only one group (the \'default\' group).
|
| The $query_builder variables lets you determine whether or not to load
| the query builder class.
*/
$active_group = \'default\';
$query_builder = TRUE;

$db[\'default\'] = array(
    \'dsn\'   => defined(\'APP_DB_DSN\') ? APP_DB_DSN : \'\',
    \'hostname\' => APP_DB_HOSTNAME,
    \'username\' => APP_DB_USERNAME,
    \'password\' => APP_DB_PASSWORD,
    \'database\' => APP_DB_NAME,
    \'dbdriver\' => defined(\'APP_DB_DRIVER\') ? APP_DB_DRIVER : \'mysqli\',
    \'dbprefix\' => \'\', // Not Supported
    \'pconnect\' => FALSE,
    \'db_debug\' => (ENVIRONMENT !== \'production\'),
    \'cache_on\' => FALSE,
    \'cachedir\' => \'\',
    \'char_set\' => \'utf8\',
    \'dbcollat\' => \'utf8_general_ci\',
    \'swap_pre\' => \'\',
    \'encrypt\' => FALSE,
    \'compress\' => FALSE,
    \'stricton\' => FALSE,
    \'failover\' => array(),
    \'save_queries\' => TRUE
);';
                    fwrite($fp, $update_old_db_config);
                    fclose($fp);
                }
            }
        }
        add_option('default_task_priority', 2);
        add_option('dropbox_app_key', '');
        add_option('auto_assign_customer_admin_after_lead_convert', 1);

        $this->db->query("ALTER TABLE  `tblinvoices` ADD  `number_format` INT NOT NULL DEFAULT  '0' AFTER  `prefix`;");

        $invoices = $this->db->get('tblinvoices')->result_array();
        foreach ($invoices as $invoice) {
            $this->db->where('id', $invoice['id']);
            $this->db->update('tblinvoices', array(
                'number_format' => get_option('invoice_number_format')
            ));
        }

        $this->db->query("ALTER TABLE  `tblestimates` ADD  `number_format` INT NOT NULL DEFAULT  '0' AFTER  `prefix`");

        $estimates = $this->db->get('tblestimates')->result_array();
        foreach ($estimates as $estimate) {
            $this->db->where('id', $estimate['id']);
            $this->db->update('tblestimates', array(
                'number_format' => get_option('estimate_number_format')
            ));
        }

        $this->db->query("CREATE TABLE IF NOT EXISTS `tblfiles` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `rel_id` int(11) NOT NULL,
                  `rel_type` varchar(20) NOT NULL,
                  `file_name` varchar(600) NOT NULL,
                  `filetype` varchar(40) DEFAULT NULL,
                  `visible_to_customer` int(11) NOT NULL DEFAULT '0',
                  `attachment_key` varchar(32) DEFAULT NULL,
                  `external` varchar(40) DEFAULT NULL,
                  `external_link` text,
                  `thumbnail_link` text COMMENT 'For external usage',
                  `staffid` int(11) NOT NULL,
                  `contact_id` int(11) DEFAULT '0',
                  `dateadded` datetime NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `rel_id` (`rel_id`),
                  KEY `rel_type` (`rel_type`)
                ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        $lead_attachments = $this->db->get('tblleadattachments')->result_array();

        foreach ($lead_attachments as $at) {
            $this->db->insert('tblfiles', array(
                'staffid' => $at['addedfrom'],
                'dateadded' => $at['dateadded'],
                'rel_id' => $at['leadid'],
                'attachment_key' => md5(uniqid(rand(), true) . $at['leadid'] . 'lead' . time()),
                'rel_type' => 'lead',
                'file_name' => $at['file_name'],
                'filetype' => $at['filetype']
            ));
        }

        $this->db->query("DROP TABLE tblleadattachments");

        $expenses = $this->db->get('tblexpenses')->result_array();
        foreach ($expenses as $expense) {
            if (!empty($expense['attachment'])) {
                $this->db->insert('tblfiles', array(
                    'staffid' => $expense['addedfrom'],
                    'dateadded' => $expense['dateadded'],
                    'rel_id' => $expense['id'],
                    'attachment_key' => md5(uniqid(rand(), true) . $expense['id'] . 'expense' . time()),
                    'rel_type' => 'expense',
                    'file_name' => $expense['attachment'],
                    'filetype' => $expense['filetype']
                ));
            }
        }

        $this->db->query("ALTER TABLE  `tblexpenses` DROP  `attachment` ,
DROP  `filetype` ;");

        $contract_attachments = $this->db->get('tblcontractattachments')->result_array();
        foreach ($contract_attachments as $at) {
            $this->db->insert('tblfiles', array(
                'staffid' => 1,
                'dateadded' => $at['dateadded'],
                'rel_id' => $at['contractid'],
                'attachment_key' => md5(uniqid(rand(), true) . $at['contractid'] . 'contract' . time()),
                'rel_type' => 'contract',
                'file_name' => $at['file_name'],
                'filetype' => $at['filetype']
            ));
        }

        $this->db->query("DROP TABLE tblcontractattachments");

        $client_attachments = $this->db->get('tblclientattachments')->result_array();
        foreach ($client_attachments as $at) {
            $this->db->insert('tblfiles', array(
                'staffid' => 1,
                'dateadded' => $at['datecreated'],
                'rel_id' => $at['clientid'],
                'attachment_key' => md5(uniqid(rand(), true) . $at['clientid'] . 'customer' . time()),
                'rel_type' => 'customer',
                'file_name' => $at['file_name'],
                'filetype' => $at['filetype']
            ));
        }

        $this->db->query("DROP TABLE tblclientattachments");


        $sales_attachments = $this->db->get('tblsalesattachments')->result_array();
        foreach ($sales_attachments as $at) {
            $this->db->insert('tblfiles', array(
                'staffid' => 1,
                'dateadded' => $at['datecreated'],
                'rel_id' => $at['rel_id'],
                'rel_type' => $at['rel_type'],
                'file_name' => $at['file_name'],
                'attachment_key' => md5(uniqid(rand(), true) . $at['rel_id'] . $at['rel_type'] . time()),
                'filetype' => $at['filetype'],
                'attachment_key' => $at['attachment_key'],
                'visible_to_customer' => $at['visible_to_customer']
            ));
        }

        $this->db->query("DROP TABLE tblsalesattachments");

        $newsfeed_attachments = $this->db->get('tblpostattachments')->result_array();
        foreach ($newsfeed_attachments as $at) {
            $this->db->insert('tblfiles', array(
                'staffid' => 1,
                'dateadded' => $at['datecreated'],
                'rel_id' => $at['postid'],
                'rel_type' => 'newsfeed_post',
                'attachment_key' => md5(uniqid(rand(), true) . $at['postid'] . 'newsfeed_post' . time()),
                'file_name' => $at['filename'],
                'filetype' => $at['filetype']
            ));
        }

        $this->db->query("DROP TABLE tblpostattachments");


        $tasks_attachments = $this->db->get('tblstafftasksattachments')->result_array();
        foreach ($tasks_attachments as $at) {
            $this->db->insert('tblfiles', array(
                'staffid' => 1,
                'dateadded' => $at['dateadded'],
                'rel_id' => $at['taskid'],
                'contact_id' => $at['contact_id'],
                'attachment_key' => md5(uniqid(rand(), true) . $at['taskid'] . 'task' . time()),
                'rel_type' => 'task',
                'file_name' => $at['file_name'],
                'filetype' => $at['filetype']
            ));
        }

        $this->db->query("DROP TABLE tblstafftasksattachments");

        $this->db->query("ALTER TABLE `tblclients` ADD `active` INT NOT NULL DEFAULT '1' AFTER `datecreated`;");

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
                ('staff', 'new-staff-created', 'english', 'New Staff Created (Welcome Email)', 'You are added as staff member', 'Hello&nbsp;{staff_firstname}&nbsp;{staff_lastname}<br /><br />You are added as member on our CRM.<br />You can login at {admin_url}<br /><br />Please use the following&nbsp;logic credentials:<br /><br />Email:&nbsp;{staff_email}<br />Password:&nbsp;{password}<br /><br />Best Regards,<br />{email_signature}', '{companyname} | CRM', '', 0, 1, 0);");


        $this->db->like('name', 'custom_company_field_', 'after');
        $cfields = $this->db->get('tbloptions')->result_array();
        $i      = 0;
        foreach ($cfields as $field) {
            $cfields[$i]['label'] = str_replace('custom_company_field_', '', $field['name']);
            $cfields[$i]['label'] = str_replace('_', ' ', $cfields[$i]['label']);
            $cfields[$i]['label'] = $cfields[$i]['label'];
            $i++;
        }
        foreach($cfields as $f){
            $this->db->insert('tblcustomfields',array(
                    'fieldto'=>'company',
                    'name'=>$f['label'],
                    'slug' => slug_it('company_' . $f['label'], array(
                        'separator' => '_'
                    )),
                    'type'=>'input',
                    'show_on_pdf'=>1,
                    'show_on_table'=>1,
                    'show_on_client_portal'=>1,
                    'disalow_client_to_edit'=>0,
                ));
                $insert_id = $this->db->insert_id();
                $this->db->insert('tblcustomfieldsvalues',array(
                    'relid'=>0,
                    'fieldid'=>$insert_id,
                    'fieldto'=>'company',
                    'value'=>$f['value'],

                ));

                $this->db->where('id',$f['id']);
                $this->db->delete('tbloptions');
        }


        $this->db->query("CREATE TABLE IF NOT EXISTS `tblitemstax` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `itemid` int(11) NOT NULL,
              `rel_id` int(11) NOT NULL,
              `rel_type` varchar(20) NOT NULL,
              `taxrate` decimal(11,2) NOT NULL,
              `taxname` varchar(100) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");

        $taxes_invoices = $this->db->get('tblinvoiceitemstaxes')->result_array();
        foreach($taxes_invoices as $t){
            $this->db->insert('tblitemstax',array(
                'rel_id'=>$t['invoice_id'],
                'rel_type'=>'invoice',
                'itemid'=>$t['itemid'],
                'taxrate'=>$t['taxrate'],
                'taxname'=>$t['taxname'],
                ));
        }

        $this->db->query("DROP TABLE tblinvoiceitemstaxes");

        $taxes_invoices = $this->db->get('tblestimateitemstaxes')->result_array();
        foreach($taxes_invoices as $t){
            $this->db->insert('tblitemstax',array(
                'rel_id'=>$t['estimate_id'],
                'rel_type'=>'estimate',
                'itemid'=>$t['itemid'],
                'taxrate'=>$t['taxrate'],
                'taxname'=>$t['taxname'],
                ));
        }

        $this->db->query("DROP TABLE tblestimateitemstaxes");

        $this->db->query("ALTER TABLE `tblstaff` ADD `email_signature` TEXT NULL AFTER `is_not_staff`;");

        $this->db->where('name','invoice_year');
        $this->db->delete('tbloptions');

        $this->db->where('name','estimate_year');
        $this->db->delete('tbloptions');

        $this->db->query("ALTER TABLE `tblinvoices` DROP `year`;");
        $this->db->query("ALTER TABLE `tblestimates` DROP `year`;");


        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
        ('client', 'contact-forgot-password', 'english', 'Forgot Password', 'Create New Password', '<h2>Create a new password</h2>\r\nForgot your password?<br /> To create a new password, just follow this link:<br /> <br /> <big><strong>{reset_password_url}</strong></big><br /> <br /> You received this email, because it was requested by a {companyname}&nbsp;user. This is part of the procedure to create a new password on the system. If you DID NOT request a new password then please ignore this email and your password will remain the same. <br /><br /> {email_signature}', '{companyname} | CRM', '', 0, 1, 0),
        ('client', 'contact-password-reseted', 'english', 'Password Reset - Confirmation', 'Your password has been changed', '<strong>You have changed your password.<br /></strong><br /> Please, keep it in your records so you don''t forget it.<br /> <br /> Your email address for login is: {contact_email}<br />If this wasnt you, please contact us.<br /><br />{email_signature}', '{companyname} | CRM', '', 0, 1, 0),
        ('client', 'contact-set-password', 'english', 'Set New Password', 'Set new password on {companyname} ', '<h2>Setup your new password on {companyname}</h2>\r\nPlease use the following link to setup your new password.<br /><br />Keep it in your records so you don''t forget it.<br /><br /> Please set your new password in 48 hours. After that you wont be able to set your password. <br /><br />You can login at: {crm_url}<br /> Your email address for login: {contact_email}<br /> <br /><big><strong>{set_password_url}</strong></big><br /> <br />{email_signature}', '{companyname} | CRM', '', 0, 1, 0),
        ('staff', 'staff-forgot-password', 'english', 'Forgot Password', 'Create New Password', '<h2>Create a new password</h2>\r\nForgot your password?<br /> To create a new password, just follow this link:<br /> <br /> <big><strong>{reset_password_url}</strong></big><br /> <br /> You received this email, because it was requested by a {companyname}&nbsp;user. This is part of the procedure to create a new password on the system. If you DID NOT request a new password then please ignore this email and your password will remain the same. <br /><br /> {email_signature}', '{companyname} | CRM', '', 0, 1, 0),
        ('staff', 'staff-password-reseted', 'english', 'Password Reset - Confirmation', 'Your password has been changed', '<strong>You have changed your password.<br /></strong><br /> Please, keep it in your records so you don''t forget it.<br /> <br /> Your email address for login is: {staff_email}<br /> If this wasnt you, please contact us.<br /><br />{email_signature}', '{companyname} | CRM', '', 0, 1, 0);");

        $this->db->query("INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('project', 'assigned-to-project', 'english', 'New Project Created (Sent to Customer Contacts)', 'New Project Created', '<p>Hello&nbsp;{contact_firstname}</p>\r\n<p>New project is assigned to your company.<br />Project Name:&nbsp;{project_name}</p>\r\n<p>You can view the project on the following link:{project_link}</p>\r\n<p>We are looking forward hearing from you.</p>\r\n<p>{email_signature}</p>', '{companyname} | CRM', NULL, 0, 1, 0);");

          update_option('update_info_message', '<div class="col-md-12">
            <div class="alert alert-success bold">
                <h4 class="bold">Hi! Thanks for updating Perfex CRM - You are using version 1.2.7</h4>
                <p>
                    This window will reload automaticaly in 10 seconds and will try to clear your browser cache, however its recomended to clear your browser cache manually.
                </p>
            </div>
        </div>
        <script>
            setTimeout(function(){
                window.location.reload();
            },10000);
        </script>
        ');

    }
}
