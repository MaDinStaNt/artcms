<?php
require_once(BASE_CLASSES_PATH. 'htmlpage.php');

class CFBapiAuthPage extends CHTMLPage {
	
	protected $FBapi;
	protected $FBauth;
	
	function CFBapiAuthPage(&$app){
		parent::__construct($app);	
		$this->FBapi = &$app->FBapi;
		$this->FBauth = &$app->FBauth;
	}
	
	function on_page_init(){
		parent::on_page_init();
		if($this->Application->User->is_logged() || $this->Application->VKauth->is_logged() || $this->Application->FBauth->is_logged())
			$this->Application->CurrentPage->internalRedirect($this->HTTP);
		
		if($code = InGet('code', false))
		{
			if(array_key_exists('fb_error', $_SESSION))
				unset($_SESSION['fb_error']);
				
			if(array_key_exists('fb_status', $_SESSION))
				unset($_SESSION['fb_status']);
				
			$res = $this->FBauth->login($code, $this->HTTP.'fb-auth');
			if($res === false || !is_array($res))
			{
				$_SESSION['fb_error'] = $this->FBauth->get_last_error();
				$this->Application->CurrentPage->internalRedirect($this->HTTP);
			}
			else 
			{
				$res = $this->FBapi->get_user('me');
				if($res !== false)
				{
					if(!$this->FBauth->dblogin_user($res))
						$_SESSION['fb_error'] = $this->VKauth->get_last_error();
					else 
						$_SESSION['fb_success'] = $this->Application->Localizer->get_string('vklogin_success');
					
					$this->Application->CurrentPage->internalRedirect($this->HTTP);
				}
				else 
				{
					$_SESSION['fb_error'] = $this->VKapi->get_last_error();
					$this->Application->CurrentPage->internalRedirect($this->HTTP);
				}
			}
		}
		else 
		{
			if(array_key_exists('fb_status', $_SESSION)){
				if($_SESSION['fb_status'] === VK_STATUS_PENDING_ANSWER)
				{
					unset($_SESSION['fb_status']);
					$this->Application->CurrentPage->internalRedirect($this->HTTP);
				}
			}else{
				$_SESSION['fb_status'] = VK_STATUS_PENDING_ANSWER;
				$this->Application->CurrentPage->internalRedirect($this->FBauth->get_login_url($this->HTTP.'fb-auth'));
			}
		}
		die();
	}
};
?>