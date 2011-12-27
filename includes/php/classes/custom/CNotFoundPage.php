<?php
require_once(BASE_CLASSES_PATH. 'frontpage.php');

class CNotFoundPage extends CFrontPage  {
	
	function CNotFoundPage(&$app, $template){
		parent::__construct($app, $template);	
	}
	
	function on_page_init(){
		parent::on_page_init();
		$this->PAGE_TITLE = $this->Application->Localizer->get_string('page_not_found');
	}
	
	function parse_data(){
		
		if(!parent::parse_data())
			return false;
		
		return true;
	}
};
?>