<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CPathEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'registry_path';
    
    protected $_module = 'Registry';
    
    protected $Registry;
    
    protected $path_id;

    function CPathEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
		$this->Registry = $this->Application->get_module('Registry');
	}

	function on_page_init()
	{
		parent::on_page_init();
	}

	function parse_data()
	{
		if (!parent::parse_data())
			return false;
			
		if($this->id) $this->bind_path_values();
		
		return true;
	}
	
	function bind_data() {
		parent::bind_data();
    	$language_rs = $this->Application->Localizer->get_languages();
    	if($language_rs == false) $language_rs = new CRecordSet();
    	$language_rs->add_row(array('id' => '', 'title' => RECORDSET_FIRST_ITEM), INSERT_BEGIN);
    	CInput::set_select_data('language_id', $language_rs, 'id', 'title');
    	
    	$path_rs = $this->Registry->get_pathes();
    	if($path_rs == false) $path_rs = new CRecordSet();
    	if($path_rs->find('id', $this->id)) $path_rs->delete_row();
    	$path_rs->add_row(array('id' => '', 'title' => RECORDSET_FIRST_ITEM), INSERT_BEGIN);
    	$path_rs->first();
    	while($path_rs->find('parent_id', -1)){$path_rs->delete_row(); $path_rs->first();}
    	$path_rs->first();
    	CInput::set_select_data('parent_id', $path_rs, 'id', 'title');
	}
	
	function bind_path_values()
	{
		 $query = "SELECT id,
			path,
			if(type = ".KEY_TYPE_CHECKBOX.", 'checkbox', 
				if(type = ".KEY_TYPE_TEXT.", 'text',
					if(type = ".KEY_TYPE_TEXTAREA.", 'textarea',
						if(type = ".KEY_TYPE_HTML.", 'htmlarea',
							if(type = ".KEY_TYPE_DATE.", 'date', 'file')
						)
					)
				)
			) as type,
			title,
			IF(required > 0, '<img src=\"".$this->tv['HTTP']."images/icons/apply.gif\" title=\"yes\" />', '<img src=\"".$this->tv['HTTP']."images/icons/apply_disabled.gif\" title=\"no\" />') as required
            FROM %prefix%registry_value
            WHERE path_id = ".$this->id;

        require_once(BASE_CLASSES_PATH . 'controls/dbnavigator.php'); // base application class
        $nav = new DBNavigator('registry_value', $query, array('path', 'type', 'title', 'required'), 'id');
        $nav->row_clickaction = $this->Application->get_module('Navi')->getUri().".path_value_edit&path_id={$this->id}&id=";
        $nav->title = 'Values';
        
        $header_num = $nav->add_header('path');
        $nav->headers[$header_num]->set_title("Path");
        $nav->headers[$header_num]->set_width("20%");
        
        $header_num = $nav->add_header('type');
        $nav->headers[$header_num]->set_title("type");
        $nav->headers[$header_num]->set_width("20%");
        
        $header_num = $nav->add_header('title');
        $nav->headers[$header_num]->set_title("Title");
        $nav->headers[$header_num]->set_width("59%");
        
        $header_num = $nav->add_header('required');
        $nav->headers[$header_num]->set_title("Required");
        $nav->headers[$header_num]->set_width("30px");
        $nav->headers[$header_num]->set_align("center");

        if($nav->size > 0)
        	$this->tv['remove_btn_show'] = true;
        else
        	$this->tv['remove_btn_show'] = false;
	}
	
	public function on_registry_path_submit($action) {
		switch ($action) {
			case 'close':
				$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('parent', false));
			break;
			case 'add_registry_value':
				$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('./path_value_edit')."&path_id={$this->id}");
			break;
			default:
				CValidator::add('language_id', VRT_NUMBER, false, 1);
				CValidator::add_nr('parent_id', VRT_NUMBER, false, 1);
				CValidator::add('path', VRT_TEXT, false, 0, 255);
				CValidator::add('title', VRT_TEXT, false, 0, 255);
				if (CValidator::validate_input()) {
					if ($this->id) {
						if ($this->Registry->update_path($this->id, $this->tv)) {
							$this->tv['_info'] = $this->Localizer->get_string('object_updated');
						}
						else {
							$this->tv['_errors'] = $this->Registry->get_last_error();
						}
					}
					else {
						if ($this->tv['id'] = $this->Registry->add_path($this->tv)) {
							$this->tv['_info'] = $this->Localizer->get_string('object_added');
							//$this->tv['_return_to'] =  $this->Application->Navi->getUri().'&id='.$this->tv['id'];
						}
						else {
							$this->tv['_errors'] = $this->Registry->get_last_error();
						}
					}
				}
				else {
					$this->tv['_errors'] = CValidator::get_errors();
				}
			break;
		}
		$this->bind_data();
		$this->bind_path_values();
	}
}
?>