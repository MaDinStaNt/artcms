<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CStringEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'loc_string';

    protected $_module = 'Localizer';

    function CStringEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
		CInput::set_select_data('language_id', $this->Application->Localizer->get_languages(), 'id', 'title');
	}

	function on_page_init()
	{
		parent::on_page_init();
		CValidator::add('name', VRT_TEXT, false, 0, 255);
		CValidator::add('value', VRT_TEXT, false, 0, 255);
	}

	function parse_data()
	{
		parent::parse_data();
	}
}
?>