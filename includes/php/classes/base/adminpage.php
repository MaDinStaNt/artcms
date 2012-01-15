<?
require_once(BASE_CLASSES_PATH. 'htmlpage.php');
class CAdminPage extends CHTMLPage
{
	function CAdminPage(&$app, $content)
	{
		global $SiteName;
		parent::CHTMLPage($app, $content);

		$this->NoCache = true;
		$this->IsSecure = true;
		$this->UserLevel = USER_LEVEL_GLOBAL_ADMIN;

		$this->h_header = '_admin_head.tpl';
		$this->h_body = '_admin_body.tpl';
		$this->h_footer = '_admin_foot.tpl';
		$this->tv['admin_page'] = true;
		if ($this->Application->User->is_logged()) {
			if ($this->Application->User->UserData['user_role_id'] < USER_LEVEL_GLOBAL_ADMIN) {
				$this->Application->User->logout();
			}
		}
		elseif (isset($this->Application->User->UserData['user_role_id']) && $this->Application->User->UserData['user_role_id'] < USER_LEVEL_GLOBAL_ADMIN){
			$this->Application->User->logout();
		}

		$this->PAGE_TITLE = $SiteName;
	}

	function on_page_init()
	{
		$res = parent::on_page_init();

		require_once(BASE_CONTROLS_PATH.'navi.php');
		new CNavi('Navi');

		require_once(BASE_CONTROLS_PATH.'breadcrumb.php');
		new NaviBreadcrumb();

		return $res;
	}

	function parse_data()
	{
		if (!parent::parse_data()) return false;

		return true;
	}
}
?>