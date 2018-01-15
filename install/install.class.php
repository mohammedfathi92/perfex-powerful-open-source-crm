<?php

error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);

@ini_set('max_execution_time', 240);
define('FOPEN_WRITE_CREATE_DESTRUCTIVE', 'wb');
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('PHPASS_HASH_STRENGTH', 8);
define('PHPASS_HASH_PORTABLE', false);

class Install
{
    private $error = '';
    private $passed_steps = array();
    private $config_path = '../application/config/app-config-sample.php';

    public function __construct()
    {
        $this->passed_steps = array(
            1 => false,
            2 => false,
            3 => false,
            4 => false
        );
    }

    public function go()
    {
        $debug       = '';
        $step        = 1;

        if (isset($_POST) && !empty($_POST)) {
            if (isset($_POST['step']) && $_POST['step'] == 2) {
                $step                  = 2;
                $this->passed_steps[1] = true;
                $this->passed_steps[2] = true;
            } elseif (isset($_POST['step']) && $_POST['step'] == 3) {
                if ($_POST['hostname'] == '') {
                    $this->error = 'Hostname is required';
                } elseif ($_POST['database'] == '') {
                    $this->error = 'Enter database name';
                } elseif ($_POST['password'] == '' && !$this->is_localhost()) {
                    $this->error = 'Enter database password';
                } elseif ($_POST['username'] == '') {
                    $this->error = 'Enter database username';
                }
                $step                  = 3;
                $this->passed_steps[1] = true;
                $this->passed_steps[2] = true;
                if ($this->error === '') {
                    $this->passed_steps[3] = true;

                    $h = trim($_POST['hostname']);
                    $u = trim($_POST['username']);
                    $p = trim($_POST['password']);
                    $d = trim($_POST['database']);

                    $link                  = @mysqli_connect($h, $u, $p, $d);
                    if (!$link) {
                        $this->error .= "Error: Unable to connect to MySQL." . PHP_EOL;
                        $this->error .= "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
                        $this->error .= "Debugging error: " . mysqli_connect_error() . PHP_EOL;
                    } else {
                        $debug .= "Success: A proper connection to MySQL was made! The " . $_POST['database'] . " database is great." . PHP_EOL;
                        $debug .= "Host information: " . mysqli_get_host_info($link) . PHP_EOL;
                        $step = 4;
                        mysqli_close($link);
                    }
                }
            } elseif (isset($_POST['requirements_success'])) {
                $step                  = 2;
                $this->passed_steps[1] = true;
                $this->passed_steps[2] = true;
            } elseif (isset($_POST['permissions_success'])) {
                $step                  = 3;
                $this->passed_steps[1] = true;
                $this->passed_steps[2] = true;
                $this->passed_steps[3] = true;
            } elseif (isset($_POST['step']) && $_POST['step'] == 4) {
                if ($_POST['admin_email'] == '') {
                    $this->error = 'Enter admin email address';
                } elseif (filter_var($_POST['admin_email'], FILTER_VALIDATE_EMAIL) === false) {
                    $this->error = 'Enter valid email address';
                } elseif ($_POST['admin_password'] == '') {
                    $this->error = 'Enter admin password';
                } elseif ($_POST['admin_password'] != $_POST['admin_passwordr']) {
                    $this->error = 'Your password not match';
                } elseif ($_POST['base_url'] == '') {
                    $this->error = 'Please enter base url';
                }
                $this->passed_steps[1] = true;
                $this->passed_steps[2] = true;
                $this->passed_steps[3] = true;
                $this->passed_steps[4] = true;
                $step                  = 4;
            }
            if ($this->error === '' && isset($_POST['step']) && $_POST['step'] == 4) {

                include_once('sqlparser.php');
                $parser = new SqlScriptParser();

                $sqlStatements = $parser->parse('database.sql');

                $h = trim($_POST['hostname']);
                $u = trim($_POST['username']);
                $p = trim($_POST['password']);
                $d = trim($_POST['database']);

                $link     = mysqli_connect($h, $u, $p, $d);
                mysqli_set_charset($link, "utf8");

                foreach ($sqlStatements as $statement) {
                    $distilled = $parser->removeComments($statement);
                    if (!empty($distilled)) {
                        $link->query($distilled);
                    }
                }

                $this->write_app_config();

                if (!$this->rename_app_config()) {
                    $rename_failed = true;
                }

                require_once('phpass.php');

                $hasher      = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
                $password    = $hasher->HashPassword($_POST['admin_passwordr']);
                $email       = $_POST['admin_email'];
                $firstname = $_POST['firstname'];
                $lastname = $_POST['lastname'];

                $datecreated = date('Y-m-d H:i:s');

                $timezone = $_POST['timezone'];
                $sql      = "UPDATE tbloptions SET value='$timezone' WHERE name='default_timezone'";
                mysqli_query($link, $sql);

                $di = time();
                $sql      = "UPDATE tbloptions SET value='$di' WHERE name='di'";
                mysqli_query($link, $sql);

                $sql = "INSERT INTO tblstaff (firstname, lastname, password, email, datecreated, admin, active) VALUES('$firstname', '$lastname', '$password', '$email', '$datecreated', 1, 1)";
                mysqli_query($link, $sql);

                $this->passed_steps[1] = true;
                $this->passed_steps[2] = true;
                $this->passed_steps[3] = true;
                $this->passed_steps[4] = true;

                if (!file_exists('../.htaccess') && is_writable('../')) {
                    fopen('../.htaccess', 'w');
                    $fp = fopen('../.htaccess', 'a+');
                    if ($fp) {
                        fwrite($fp, 'RewriteEngine on'.PHP_EOL.'RewriteCond $1 !^(index\.php|resources|robots\.txt)'.PHP_EOL.'RewriteCond %{REQUEST_FILENAME} !-f'.PHP_EOL.'RewriteCond %{REQUEST_FILENAME} !-d'.PHP_EOL.'RewriteRule ^(.*)$ index.php?/$1 [L,QSA]'.PHP_EOL.'AddDefaultCharset utf-8');
                        fclose($fp);
                    }
                }
                $step                  = 5;
            } else {
                $error = $this->error;
            }
        }
        $passed_steps = $this->passed_steps;
        require_once('html.php');
    }

    public function is_localhost()
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

        if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)) {
            return true;
        }

        return false;
    }

    private function write_app_config()
    {
        $hostname       = trim($_POST['hostname']);
        $database       = trim($_POST['database']);
        $username       = trim($_POST['username']);
        $password       = trim($_POST['password']);

        $base_url = trim($_POST['base_url']);
        $base_url       = rtrim($base_url, '/') . '/';

        $encryption_key = bin2hex($this->create_key(16));
        $config_path    = $this->config_path;

        @chmod($config_path, FILE_WRITE_MODE);

        $config_file = file_get_contents($config_path);
        $config_file = trim($config_file);

        $config_file = str_replace("define('APP_DB_HOSTNAME','localhost')", "define('APP_DB_HOSTNAME','" . $hostname . "')", $config_file);

        $config_file = str_replace("define('APP_DB_USERNAME','')", "define('APP_DB_USERNAME','" . $username . "')", $config_file);
        $config_file = str_replace("define('APP_DB_PASSWORD','')", "define('APP_DB_PASSWORD','" . $password . "')", $config_file);
        $config_file = str_replace("define('APP_DB_NAME','')", "define('APP_DB_NAME','" . $database . "')", $config_file);
        $config_file = str_replace("define('APP_ENC_KEY','')", "define('APP_ENC_KEY','" . $encryption_key . "')", $config_file);
        $config_file = str_replace("define('APP_BASE_URL','')", "define('APP_BASE_URL','" . $base_url . "')", $config_file);

        if (!$fp = fopen($config_path, FOPEN_WRITE_CREATE_DESTRUCTIVE)) {
            return false;
        }

        flock($fp, LOCK_EX);
        fwrite($fp, $config_file, strlen($config_file));
        flock($fp, LOCK_UN);
        fclose($fp);
        @chmod($config_path, FILE_READ_MODE);

        return true;
    }

    private function rename_app_config()
    {
        if (@rename('../application/config/app-config-sample.php', '../application/config/app-config.php') == true) {
            return true;
        }

        return false;
    }

    public function create_key($length)
    {
        if (function_exists('random_bytes')) {
            try {
                return random_bytes((int) $length);
            } catch (Exception $e) {
                echo $e->getMessage();

                return false;
            }
        } elseif (defined('MCRYPT_DEV_URANDOM')) {
            return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
        }

        $is_secure = null;
        $key       = openssl_random_pseudo_bytes($length, $is_secure);

        return ($is_secure === true) ? $key : false;
    }

    public function guess_base_url()
    {
        $base_url = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on' ? 'https' : 'http';
        $base_url .= '://'. $_SERVER['HTTP_HOST'];
        $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
        $base_url = preg_replace('/install.*/', '', $base_url);

        return $base_url;
    }

    public function get_timezones_list()
    {
        return array(
        'EUROPE'=>DateTimeZone::listIdentifiers(DateTimeZone::EUROPE),
        'AMERICA'=>DateTimeZone::listIdentifiers(DateTimeZone::AMERICA),
        'INDIAN'=>DateTimeZone::listIdentifiers(DateTimeZone::INDIAN),
        'AUSTRALIA'=>DateTimeZone::listIdentifiers(DateTimeZone::AUSTRALIA),
        'ASIA'=>DateTimeZone::listIdentifiers(DateTimeZone::ASIA),
        'AFRICA'=>DateTimeZone::listIdentifiers(DateTimeZone::AFRICA),
        'ANTARCTICA'=>DateTimeZone::listIdentifiers(DateTimeZone::ANTARCTICA),
        'ARCTIC'=>DateTimeZone::listIdentifiers(DateTimeZone::ARCTIC),
        'ATLANTIC'=>DateTimeZone::listIdentifiers(DateTimeZone::ATLANTIC),
        'PACIFIC'=>DateTimeZone::listIdentifiers(DateTimeZone::PACIFIC),
        'UTC'=>DateTimeZone::listIdentifiers(DateTimeZone::UTC),
        );
    }
}
