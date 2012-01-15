<?php
require_once(BASE_CLASSES_PATH. 'frontpage.php');

class CIndexPage extends CFrontPage {
	
	protected $user_rs;
	
	function CIndexPage(&$app, $template){
		parent::__construct($app, $template);	
	}
	
	function on_page_init(){
		parent::on_page_init();
		//unset($_SESSION['UserData']);
	}
	
	function parse_data(){
		
		if(!parent::parse_data())
			return false;
			
		$this->tv['test_var'] = "\\\
		<div><strong>hu</strong>i</div><br />
		<script type=\"text/javascript\">
			alert(1);
		</script>";
		
		return true;
	}
	
	function load_ajax_validator($field)
	{
		switch ($field)
		{
			case 'ajax_test':
				CValidator::add('ajax_test', VRT_TEXT, false, 3, 10);
			break;
		}
	}
};
?>