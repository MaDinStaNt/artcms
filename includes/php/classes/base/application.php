<?php
/**
 * @package LLA.Base
 */
/**
 */
require_once('object.php');
require_once('template.php');
if (APPLICATION_DATABASE == 'mysql') require_once('database.mysql.php'); // mysql database
elseif (APPLICATION_DATABASE == 'pgsql') require_once('database.postgresql.php'); // postgre database
elseif (APPLICATION_DATABASE == 'mssql') require_once('database.mssql.php'); // MS SQL database
elseif (APPLICATION_DATABASE == 'db2') require_once('database.db2.php'); // IBM DB2 database
require_once('user.php');
require_once('localizer.php');
require_once('validator.php');
require_once('router.php');
require_once('session.php');
require_once('utils.php');
//require_once('controls/form.php');
/**
 * Main class for the site, extend CApp from it<br>
 * properties:<br>
 *         User - reference to the instance of CUser class<br>
 *         DataBase - reference to the instance of CDataBase class<br>
 *         CurrentPage - reference to the instance of current running CHTMLPage<br>
 * events:<br>
 *         implement on_page_init to declare global controls<br>
 * history:<br>
 *         v 1.3.0 - garbage collector (VK)<br>
 *         v 1.2.2 - HTML/XHTML (VK)<br>
 *         v 1.2.1 - validator support (VK)<br>
 *         v 1.2.0 - events added (VK)<br>
 *         v 1.1.0 - safe module handling (VK)<br>
 *         v 1.0.0 - created (VK)<br>
 * @package LLA.Base
 * @version 1.30
 */
class CApplication {
        var $Modules;
        /**
         * @var CDataBase
         */
        var $DataBase;
        var $User;
        var $Router;
        var $Session;
        var $Localizer;
        var $CurrentPage;
        var $template_vars;
        var $tv;
        var $Smarty;
        var $events;
        var $locale;
        var $codepage;
        function CApplication() {
                $this->Modules = array();
                $this->events = array();
                $this->tv = array();
                $this->Modules['BF'] = array('ClassName' => 'CBlowFish', 'ClassPath' => BASE_CLASSES_PATH . 'components/blowfish.php', 'Title'=>'');
                $this->Modules['Thumbnail'] = array('ClassName' => 'CThumbnail', 'ClassPath' => BASE_CLASSES_PATH . 'components/thumbnail.php', 'Title'=>'');
                $this->DataBase = new CDataBase($this, $this->locale);
                $this->User = new CUser($this);
                $this->Localizer = new CLocalizer($this);
                $this->Router = new CRouter($this);
                //$this->Session = new CSession($this);
                $this->Modules['DataBase'] = &$this->DataBase;
                $this->Modules['User'] = &$this->User;
                $this->Modules['Router'] = &$this->Router;
                //$this->Modules['Session'] = &$this->Session;
                $this->Modules['Localizer'] = &$this->Localizer;
                $this->Modules['Registry'] = array('ClassName' => 'CRegistry', 'ClassPath' => BASE_CLASSES_PATH . 'registry.php', 'Title'=>'Settings');

	            $this->Modules['Navi'] = array(
	                    'ClassName' => 'CNaviModule',
	                    'ClassPath' => CUSTOM_CLASSES_PATH . 'components/navi.php',
	                    'Visual'=>'0',
	                    'Title' => 'Navigation'
	            );
                $this->Navi = $this->get_module('Navi');

                $upm = @ini_get('upload_max_filesize');
                if (preg_match('/g$/i', $upm)) $upm = intval($upm) * 1024 * 1024 * 1024;
                elseif (preg_match('/m$/i', $upm)) $upm = intval($upm) * 1024 * 1024;
                elseif (preg_match('/k$/i', $upm)) $upm = intval($upm) * 1024;
                else $upm = intval($upm);
                //$this->tv->assign['MAX_FILE_SIZE'] = $upm;

                $agent = $this->get_server_var('HTTP_USER_AGENT');
        }
        function get_server_var($name)
        {
                return ( (isset($_SERVER[$name]))?($_SERVER[$name]):('') );
        }
        function is_module($mod_name) {// check module presens in system
                return (isset($this->Modules[$mod_name]));
        }
        function &get_module($mod_name, $ajax_check = false) {// returns reference to the module
                if (isset($this->Modules[$mod_name])){
                        if (is_object($this->Modules[$mod_name]) && (!$ajax_check || $this->Modules[$mod_name]->ajax_visible === true))
                        	return $this->Modules[$mod_name];
                        elseif (is_array($this->Modules[$mod_name]) && (!$ajax_check || $this->Modules[$mod_name]['AjaxVisible'] === true)) {
                        	$class_name = $this->Modules[$mod_name]['ClassName'];
                            require_once($this->Modules[$mod_name]['ClassPath']);
                            $this->Modules[$mod_name] = new $class_name($this);
                            return $this->Modules[$mod_name];
                        }
                        else system_die('No such module '.$mod_name);
                }
                else system_die('No such module '.$mod_name);
        }
        function &get_page() {
                return $this->CurrentPage;
        }
        function check_install()
        {
        	
        }
        
    function event_raise($event_name, $event_content = null) {
        $res = true;
        if (isset($this->events[$event_name])) {
                foreach ($this->events[$event_name] as $k => $v) {
                           $mod = &$this->get_module($v);
                if(method_exists($mod, 'event_trap')) $res &= $mod->event_trap($event_name, $event_content);
            }
        }
        return $res;
    }
        function on_page_init() {// implement this function in CApp class for declaration of global controls
                return true;
        }
        function on_page_done() {// implement this function in CApp class for deinitialization of global controls
                $this->_destroy_objects();
                return true;
        }
        function on_install_module($module_name) {// implement this function in CApp to declare additional inititlization
                include('application.setup.php');
                return true;
        }

        // internal
        var $_objects = array();
        function _add_object(&$o) {
                $this->_objects[] = &$o;
        }
        function _destroy_objects() {
                foreach ($this->_objects as $k => $v)
                        if (method_exists($v, 'on_destroy'))
                                $this->_objects[$k]->on_destroy();
        }
        function add_submodule($submodule, $submoduleparam)
        {
            $this->tv['submodules'][] = $submodule;
            $this->tv['submodulesparam'][] = $submoduleparam;
            if(InPostGet('submodule','') == $submoduleparam)
            {
                $this->tv['submodulesact'][] = true;
                $this->tv['current_submodule'] = $submoduleparam;
                $this->tv['currentsubmodule'] = $submodule;
            }
            else
            {
                $this->tv['submodulesact'][] = false;
            }
            $this->tv['submodules_cnt'] = sizeof($this->tv['submodules']);
        }
        
        
}

if (!function_exists('session_start'))
{
        system_die('Session module is not installed');
}
?>