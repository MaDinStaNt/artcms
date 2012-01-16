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
		/*$ch = curl_init('http://xpgraph.com/i/xpgraph.png');
		$res = $fp = fopen(ROOT.'pub/xpgraph.png', 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);*/
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