<?php
require_once(BASE_CLASSES_PATH. 'htmlpage.php');

class CVKapiAuthPage extends CHTMLPage {
	
	protected $VKapi;
	protected $VKauth;
	
	function CVKapiAuthPage(&$app){
		parent::__construct($app);	
		$this->VKapi = &$app->VKapi;
		$this->VKauth = &$app->VKauth;
	}
	
	function on_page_init(){
		parent::on_page_init();
		if($this->Application->User->is_logged() || $this->Application->VKauth->is_logged())
			$this->Application->CurrentPage->internalRedirect($this->HTTP);
		
		if($code = InGet('code', false))
		{
			if(array_key_exists('vk_error', $_SESSION))
				unset($_SESSION['vk_error']);
				
			if(array_key_exists('vk_status', $_SESSION))
				unset($_SESSION['vk_status']);
				
			$res = $this->VKauth->login($code);
			if($res === false || !is_array($res))
			{
				$_SESSION['vk_error'] = $this->VKauth->get_last_error();
				$this->Application->CurrentPage->internalRedirect($this->HTTP);
			}
			else 
			{
				$res = $this->VKapi->get_user($res['user_id']);
				if($res !== false)
				{
					$res = $res[0];
					if(!$this->VKauth->dblogin_user($res))
						$_SESSION['vk_error'] = $this->VKauth->get_last_error();
					else 
						$_SESSION['vk_success'] = $this->Application->Localizer->get_string('vklogin_success');
					
					$this->Application->CurrentPage->internalRedirect($this->HTTP);
				}
				else 
				{
					$_SESSION['vk_error'] = $this->VKapi->get_last_error();
					$this->Application->CurrentPage->internalRedirect($this->HTTP);
				}
			}
		}
		else 
		{
			if(array_key_exists('vk_status', $_SESSION)){
				if($_SESSION['vk_status'] === VK_STATUS_PENDING_ANSWER)
				{
					unset($_SESSION['vk_status']);
					$this->Application->CurrentPage->internalRedirect($this->HTTP);
				}
			}else{
				$_SESSION['vk_status'] = VK_STATUS_PENDING_ANSWER;
				$this->Application->CurrentPage->internalRedirect($this->VKauth->get_login_url($this->HTTP.'vk-auth'));
			}
		}
		die();
	}
};
?>