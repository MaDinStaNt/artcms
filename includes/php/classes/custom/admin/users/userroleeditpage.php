<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CUserRoleEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'user_role';

    protected $_module = 'User';

    function CUserRoleEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
	}

	function on_page_init()
	{
		parent::on_page_init();
		CValidator::add('id', VRT_NUMBER, false, 1, 255);
		CValidator::add('title', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('description', VRT_TEXT, false, 0, 255);
	}

	function parse_data()
	{
		parent::parse_data();
	}
}
?>