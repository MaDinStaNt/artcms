<?php
require_once(BASE_CLASSES_PATH . 'adminpage.php');


class CMasterEditPage extends CAdminPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_object_rs = null;
	
	function CMasterEditPage(&$app, $template)
	{
		parent::CAdminPage($app, $template);
		$this->DataBase = &$this->Application->DataBase;
		$this->Localizer = &$this->Application->Localizer;
		$this->User = &$this->Application->User;
	}
	
	function on_page_init()
	{
		parent::on_page_init();
		$this->IsSecure = true;
		$this->bind_data();
	}
	
	function bind_data() {
		$this->tv['_table'] = $this->_table;

		if(!$this->tv['id'])
			$this->tv['id'] = (int)InGetPost('id');
		
		$this->id = $this->tv['id'];
		if ($this->id) {
			if (!$this->_object_rs = $this->is_object_exists($this->id))
			{
				$this->tv['_errors'] = $this->Localizer->get_string('object_not_exist');
				$this->h_content = '';
			}
			else {
				row_to_vars($this->_object_rs, $this->tv);
			}
		}
	}
	
	function parse_data()
	{
		if (!parent::parse_data()) return false;

		$mod = $this->Application->get_module($this->_module);
		if (CForm::is_submit($this->_table)) {
			if (CValidator::validate_input()) {
				if ($this->id) {
					if ($mod->{'update_'.$this->_table}($this->id, $this->tv)) {
						$this->tv['_info'] = $this->Localizer->get_string('object_updated');
						$this->tv['_return_to'] =  $this->Application->Navi->getUri('parent', false);
					}
					else {
						$this->tv['_errors'] = $mod->get_last_error();
					}
				}
				else {
					if ($this->tv['id'] = $mod->{'add_'.$this->_table}($this->tv)) {
						$this->tv['_info'] = $this->Localizer->get_string('object_added');
						$this->tv['_return_to'] =  $this->Application->Navi->getUri('parent', false);
					}
					else {
						$this->tv['_errors'] = $mod->get_last_error();
					}
				}
				$this->bind_data();
			}
			else {
				$this->tv['_errors'] = CValidator::get_errors();
			}
		}
		elseif (CForm::is_submit($this->_table, 'close')) {
			$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('parent', false));
		} 
	
		return true;
	}

	function is_object_exists($id)
	{
		$object_rs = $this->DataBase->select_sql($this->_table, array('id' => $id));
		if (($object_rs !== false)&&(!$object_rs->eof()))
			return $object_rs;
		else 
			return false;
	}
	
	function get_status_array()
	{
		return array(OBJECT_ACTIVE => $this->Application->Localizer->get_string('active'), OBJECT_NOT_ACTIVE => $this->Application->Localizer->get_string('inactive'));
	}
	
	function get_user_status_array()
	{
		return array(OBJECT_ACTIVE => $this->Application->Localizer->get_string('active'), OBJECT_NOT_ACTIVE => $this->Application->Localizer->get_string('inactive'), OBJECT_SUSPENDED => $this->Application->Localizer->get_string('suspended'));
	}
}

?>