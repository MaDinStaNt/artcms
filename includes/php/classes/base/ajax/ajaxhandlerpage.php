<?php
require_once(BASE_CLASSES_PATH.'htmlpage.php');
class CAjaxHandlerPage extends CHTMLPage  
{
	function CAjaxHandlerPage(&$app, $template)
	{
		$this->Application = &$app;
		parent::CHTMLPage($app, $template);
	}

	public function on_page_init()
	{
		parent::on_page_init();
		if ($bll_class_name = InGetPost('bll_class_name', false)) {
			$this->Mod =& $this->Application->get_module(InGetPost('bll_class_name', false), true);
			if ($method_name = InGetPost('method_name', false)) {
				$res = call_user_method_array($method_name, $this->Mod, InGetPost('parameters', array()));
				$return = array('response' => $res);
				if($res == false)
					$return['errors'] = $this->Mod->get_last_error();
					
				echo json_encode($return);
				die();
			}
		}
	}

	public function parse_data()
	{
		if (!parent::parse_data())
			return false;
			
		return true;
	}
}

?>