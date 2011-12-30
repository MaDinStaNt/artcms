<?php
require_once('config/_config.inc.php');
require_once(FUNCTION_PATH . 'functions.php');
require_once(BASE_CLASSES_PATH . 'application.php');

define('OBJECT_ACTIVE', 1);
define('OBJECT_NOT_ACTIVE', 0);
define('OBJECT_SUSPENDED', 2);

define('TH_IMAGE_WIDTH', 150);
define('TH_IMAGE_HEIGHT', 225);

define('MD_IMAGE_WIDTH', 250);
define('MD_IMAGE_HEIGHT', 375);

define('OR_IMAGE_WIDTH', 60);
define('OR_IMAGE_HEIGHT', 90);


define('RECORDSET_FIRST_ITEM', '- Select from the list -');

class CApp extends CApplication
{
    function CApp()
    {
		//$this->locale = 'ru_RU';
		
    	//$this->codepage = 'CP1251';
        //setlocale(LC_ALL, ((!isset($_SERVER['WINDIR'])) ? sprintf('%1$s.%2$s', $this->locale, $this->codepage) : ''));	
        parent::CApplication();
        
        $this->Modules['Images'] = array(
			'ClassName' => 'CImages',
            'ClassPath' => CUSTOM_CLASSES_PATH . 'components/images.php',
            'Visual'=>'0',
            'Title' => 'Images'
        );
        $this->Modules['Inputs'] = array(
			'ClassName' => 'CInputs',
            'ClassPath' => CUSTOM_CLASSES_PATH . 'components/inputs.php',
            'Visual'=>'0',
            'AjaxVisible' => true,
            'Title' => 'Inputs'
        );
        
        
   	}
    
    function on_page_init() {
        if (!parent::on_page_init())
                return false;
        global $DebugLevel;
        global $SiteName;

	    $client_ip = ( !empty($HTTP_SERVER_VARS['REMOTE_ADDR']) ) ? $HTTP_SERVER_VARS['REMOTE_ADDR'] : ( ( !empty($HTTP_ENV_VARS['REMOTE_ADDR']) ) ? $HTTP_ENV_VARS['REMOTE_ADDR'] : getenv('REMOTE_ADDR') );
	    //$this->Session->session_notify($client_ip);

		$this->tv['copyright_year'] = date('Y');
		$this->tv['last_modified'] = gmdate('D, d M Y 00:00:00', time() - 24*60*60) . ' GMT';
		$this->tv['is_debug_mode'] = ($DebugLevel == 255);
		$this->tv['site_name'] = $SiteName;
		
		return true;
    }
}

function on_php_error($code, $message, $filename='', $linenumber=-1, $context=array()) {
    if (intval($code) != 2048)
    {
      //  system_die('Error '.$code.' ('.$message.') occured in '.$filename.' at '.$linenumber.'');
    }
}
@ob_start();
@set_error_handler('on_php_error');
@session_start();
$GLOBALS['app'] = new CApp();
?>