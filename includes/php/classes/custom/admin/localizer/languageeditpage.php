<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CLanguageEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'loc_lang';

    protected $_module = 'Localizer';

    function CStringEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
	}

	function on_page_init()
	{
		parent::on_page_init();
		CValidator::add('title', VRT_TEXT, false, 0, 64);
		CValidator::add('abbreviation', VRT_TEXT, false, 0, 6);
	}

	function parse_data()
	{
		parent::parse_data();
	}
}
?>