<?
require_once(BASE_CLASSES_PATH. 'htmlpage.php');
class CFrontPage extends CHTMLPage
{

	protected $Registry;
	public $site_sett_rs;
	
	function CFrontPage(&$app, $content)
	{
		parent::CHTMLPage($app, $content);
	}

	function on_page_init()
	{
		$res = parent::on_page_init();
		return $res;
	}

	function parse_data()
	{
		if (!parent::parse_data()) return false;

		return true;
	}
}
?>