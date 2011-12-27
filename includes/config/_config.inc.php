<?php
function get_formatted_microtime() {
list($usec, $sec) = explode(' ', microtime());return ($usec+$sec);}
$_t1 = get_formatted_microtime();

$DebugLevel = 255;
$SiteName = 'Art-cms';
$SiteUrl = $_SERVER['SERVER_NAME']; 
$HTTPSSiteUrl = $_SERVER['SERVER_NAME']; 

// ie. 'http://' . $SiteUrl . $RootPath . 'filename.html';
$RootPath = '/'; // root path for http pages
$ssl_root = '/'; // root path for https pages

$HttpPort = '80';
$HttpName = 'http';

$SHttpPort = '443';
$SHttpName = 'https';

//-------------------------------------------------------------------------------------------------------------------
$AdministratorEmail = 'info@doamin.com';
$AdministratorName = 'Art-cms';
//-------------------------------------------------------------------------------------------------------------------

define('PROD', '1');
define('MOD_REWRITE', false); 

// DB variables
define('APPLICATION_DATABASE', 'mysql'); // mysql or mssql
define('DB_SERVER', 'localhost'); // db server
define('DB_PORT', '3306'); // db port: 3306 - mysql, 5432 - pgsql, 1433 - mssql
define('DB_USER', 'root'); // db user name
define('DB_PASSWORD', 'root'); // db password
define('DB_DATABASE', 'artcms'); // database name, will be created automatically
define('DB_PREFIX', ''); // prexif for name of the tables in db

define('XHTML', '1');

// proxy settings for CURL
$CUrlProxy = ''; // proxy server, example: 'proxy.com:3128'
$CUrlProxyUserName = ''; // user of proxy server
$CUrlProxyPassword = ''; // password for proxy server

//-------------------------------------------------------------------------------------------------------------------

// server properties
//$FilePath = str_replace(str_replace('\\', '/', $_SERVER['SCRIPT_NAME']), '', str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']));
//$FilePath = 'c:/sources/php/lla';
//if (substr($FilePath, -1) != '/')
//        $FilePath .= '/';
$FilePath = $_SERVER["DOCUMENT_ROOT"];
if (substr($FilePath, -1) != "/")
        $FilePath .= "/";

// you can add path to this variable if needed
$FilePath .= '';

if ( (isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] == 'on') )
        $FilePath .= ''; // change this string to https sub-path

$CSSPath = $RootPath . 'css/';
$JSPath = $RootPath . 'js/';
$ImagesPath = $RootPath . 'images/';
if ( (isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] == 'on') )
{
        $CSSPath = $ssl_root . 'css/';
        $JSPath = $ssl_root . 'js/';
        $ImagesPath = $ssl_root . 'images/';
}

define('BR', '<br />');
define('REGISTRY_FILES_WEB', '_r/');
define('REGISTRY_FILES_STORAGE', $FilePath . '_r/');
define('REGISTRY_XML', $FilePath . 'includes/registry/');

define('FUNCTION_PATH', $FilePath . 'includes/php/functions/');

define('ROUTES_PATH', $FilePath . 'includes/routes/');

define('BASE_CLASSES_PATH', $FilePath . 'includes/php/classes/base/');
define('BASE_CONTROLS_PATH', $FilePath . 'includes/php/classes/base/controls/');
define('CUSTOM_CLASSES_PATH', $FilePath . 'includes/php/classes/custom/');
define('CUSTOM_CONTROLS_PATH', $FilePath . 'includes/php/classes/custom/controls/');

define('BASE_TEMPLATE_PATH', $FilePath . 'includes/templates/base/');
define('CUSTOM_TEMPLATE_PATH', $FilePath . 'includes/templates/custom/');
define('BASE_CONTROLS_TEMPLATE_PATH', $FilePath . 'includes/templates/base/controls/');
define('CUSTOM_CONTROLS_TEMPLATE_PATH', $FilePath . 'includes/templates/custom/controls/');

define('ROOT', $FilePath);

@ini_set('session.use_only_cookies', '1');
@ini_set('arg_separator.output', '&amp;');
@ini_set('magic_quotes_runtime', '0');
@ini_set('magic_quotes_gpc', '0');

// remove this section on production server
if (isset($_COOKIE['DL'])) $DebugLevel = $_COOKIE['DL'];
if (isset($_GET['debug'])) $DebugLevel = ((strcasecmp($_GET['debug'], '1')==0)?(255):(0));
@setcookie('DL', $DebugLevel, time()+60*60*24*30, '/');
// end of section for removal

if ($DebugLevel) {
        @error_reporting(E_ALL);
        @ini_set('display_errors', '1');
        @ini_set('display_startup_errors', '1');
}
else {
        @error_reporting(0);
        @ini_set('display_errors', '0');
        @ini_set('display_startup_errors', '0');
}
@set_time_limit(120);
?>
