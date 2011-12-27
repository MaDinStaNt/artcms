<?php
require_once(BASE_CLASSES_PATH. 'frontpage.php');

class CIndexPage extends CFrontPage {
	
	protected $user_rs;
	
	function CIndexPage(&$app, $template){
		parent::__construct($app, $template);	
	}
	
	function on_page_init(){
		parent::on_page_init();
	}
	
	function parse_data(){
		
		if(!parent::parse_data())
			return false;
		
		return true;
	}
};
?>