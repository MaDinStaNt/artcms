<?php
require_once(BASE_CLASSES_PATH. 'htmlpage.php');

class CIndexPage extends CHTMLPage {
	
	protected $user_rs;
	
	function CIndexPage(&$app, $template){
		parent::__construct($app, $template);	
		//phpinfo();
	}
	
	function on_page_init(){
		
	}
	
	function parse_data(){
		
		if(!parent::parse_data())
			return false;
		
		return true;
	}
};
?>