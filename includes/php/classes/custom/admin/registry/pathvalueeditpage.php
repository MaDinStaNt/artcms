<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CPathValueEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'registry_value';
    
    protected $_module = 'Registry';
    
    protected $Registry;

    function CPathValueEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
		$this->Registry = $this->Application->get_module('Registry');
	}

	function on_page_init()
	{
		parent::on_page_init();
		$this->bind_data();
		CValidator::add('path_id', VRT_NUMBER, false, 1);
		CValidator::add('type', VRT_NUMBER, false, 1);
		CValidator::add('path', VRT_TEXT, false, 0, 255);
		CValidator::add('title', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('value', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('valid_type', VRT_NUMBER, false, 1);
		CValidator::add_nr('valid_add_info1', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('valid_add_info2', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('valid_add_info3', VRT_TEXT, false, 0, 255);
	}

	function parse_data()
	{
		if (!parent::parse_data())
			return false;
		
		return true;
	}
	
	function bind_data()
	{
		$this->IsSecure = true;
		parent::bind_data();
		$this->path_id = $this->tv['path_id'] = InGet('path_id', '');
		if($this->id)
		{
			$validator_rs = $this->Application->DataBase->select_sql('registry_meta', array('value_id' => $this->id));
			if($validator_rs !== false && !$validator_rs->eof()) row_to_vars($validator_rs, $this->tv, false, 'valid_');
		}
		if(strlen($this->path_id) > 0)
		{
			$path_rs = $this->Application->DataBase->select_sql('registry_path', array('id' => $this->path_id));
			if($path_rs !== false || !$path_rs->eof()) $this->tv['path_title'] = $path_rs->get_field('title');
		}
		
		$path_rs = $this->Registry->get_pathes();
		if($path_rs == false || $path_rs->eof()) $path_rs = new CRecordSet();
		$path_rs->add_row(array('id' => '', 'title' => RECORDSET_FIRST_ITEM), INSERT_BEGIN);
		CInput::set_select_data('path_id', $path_rs, 'id', 'title');
		
		$type_rs = new CRecordSet();
		$type_rs->add_row(array('id' => '', 'title' => RECORDSET_FIRST_ITEM));
		$type_rs->add_row(array('id' => KEY_TYPE_CHECKBOX, 'title' => $this->Application->Localizer->get_string('checkbox')));
		$type_rs->add_row(array('id' => KEY_TYPE_TEXT, 'title' => $this->Application->Localizer->get_string('text')));
		$type_rs->add_row(array('id' => KEY_TYPE_DATE, 'title' => $this->Application->Localizer->get_string('date')));
		$type_rs->add_row(array('id' => KEY_TYPE_FILE, 'title' => $this->Application->Localizer->get_string('file')));
		$type_rs->add_row(array('id' => KEY_TYPE_IMAGE, 'title' => $this->Application->Localizer->get_string('image')));
		$type_rs->add_row(array('id' => KEY_TYPE_HTML, 'title' => $this->Application->Localizer->get_string('htmlarea')));
		$type_rs->add_row(array('id' => KEY_TYPE_TEXTAREA, 'title' => $this->Application->Localizer->get_string('textarea')));
		
		
		CInput::set_select_data('type', $type_rs, 'id', 'title');
		
		$valid_type_rs = new CRecordSet();
		$valid_type_rs->add_row(array('id' => '', 'title' => RECORDSET_FIRST_ITEM));
		$valid_type_rs->add_row(array('id' => VRT_TEXT, 'title' => $this->Application->Localizer->get_string('text')));
		$valid_type_rs->add_row(array('id' => VRT_NUMBER, 'title' => $this->Application->Localizer->get_string('number')));
		$valid_type_rs->add_row(array('id' => VRT_CUSTOM_FILE, 'title' => $this->Application->Localizer->get_string('custom_file')));
		$valid_type_rs->add_row(array('id' => VRT_IMAGE_FILE, 'title' => $this->Application->Localizer->get_string('image_file')));
		$valid_type_rs->add_row(array('id' => VRT_EMAIL, 'title' => $this->Application->Localizer->get_string('email')));
		$valid_type_rs->add_row(array('id' => VRT_ODBCDATE, 'title' => $this->Application->Localizer->get_string('odbcdate')));
		CInput::set_select_data('valid_type', $valid_type_rs, 'id', 'title');
	}
	
}
?>