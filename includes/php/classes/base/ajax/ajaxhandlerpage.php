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
				echo json_encode(call_user_method_array($method_name, $this->Mod, InGetPost('parameters', array())));
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