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
		
	}

	function on_page_init()
	{
		parent::on_page_init();
		
		$lang_rs = $this->Application->Localizer->get_languages();
		if($lang_rs == false) $lang_rs = new CRecordSet();
		CInput::set_select_data('language_id', $lang_rs, 'id', 'title');
		
		CValidator::add('language_id', VRT_ENUMERATION, false, $lang_rs, 'id');
		CValidator::add('name', VRT_TEXT, false, 0, 255);
		CValidator::add('value', VRT_TEXT, false, 0, 255);
	}

	function parse_data()
	{
		parent::parse_data();
	}
}
?>