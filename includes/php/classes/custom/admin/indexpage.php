<?
require_once(CUSTOM_CLASSES_PATH . 'admin/masterpage.php');

class CIndexPage extends CAdminPage
{
	function CIndexPage(&$app, $template)
	{
		parent::CAdminPage($app, $template);
		$this->IsSecure = true;
	}

	function on_page_init() 
	{
		parent::on_page_init();
	}

	function parse_data()
	{
		if (!parent::parse_data()) return false;
		
        return true;
	}
}
?>