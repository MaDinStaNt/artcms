<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');
require_once(BASE_CONTROLS_PATH . 'twolists.php');

class CUserGroupEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'user_group';

    protected $_module = 'User';

    function CUserGroupEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
	}

	function on_page_init()
	{
		parent::on_page_init();
		CValidator::add('title', VRT_TEXT, 0, 255);
		CValidator::add_nr('description', VRT_TEXT, '', 0, 255);
		$selected_roles_arr = array();
		if ($this->id) {
			$roles_selected_rs = $this->Application->User->get_group_role_ids($this->id);
			if ($roles_selected_rs) {
				while (!$roles_selected_rs->eof()) {
					$selected_roles_arr[$roles_selected_rs->get_field('id')] = $roles_selected_rs->get_field('title');
					$roles_selected_rs->next();
				}
			}
		}
		$available_roles_arr = array();
		$roles_rs = $this->Application->User->get_user_roles();
		if ($roles_rs) {
			while (!$roles_rs->eof()) {
				if (!array_key_exists($roles_rs->get_field('id'), $selected_roles_arr)) {
					$available_roles_arr[$roles_rs->get_field('id')] = $roles_rs->get_field('title');
				}
				$roles_rs->next();
			}
		}
		new CTwoLists('roles', $available_roles_arr, $selected_roles_arr);
	}

	function on_user_group_submit($action) {
		if ($action == "close") {
			$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('parent', false));
		}
		else {
			$mod = $this->Application->get_module($this->_module);
			if (CValidator::validate_input()) {
				if ($this->id) {
					if ($mod->{'update_'.$this->_table}($this->id, $this->tv)) {
						//add links
						$this->Application->DataBase->delete_sql('user_group_user_role_link', array('user_group_id' => $this->id));
						if (strlen($this->tv['twolists_right_roles_idx']) > 0) {
							$roles_ids_arr = explode(";", substr($this->tv['twolists_right_roles_idx'], 0, strlen($this->tv['twolists_right_roles_idx']) - 1));
							foreach ($roles_ids_arr as $user_role_id) {
								$this->Application->DataBase->insert_sql('user_group_user_role_link', array('user_group_id' => $this->id, 'user_role_id' => $user_role_id));
							}
						}
						$this->tv['_info'] = $this->Localizer->get_string('object_updated');
						$this->tv['_return_info'] =  $this->Application->Navi->getUri('parent', false);
					}
					else {
						$this->tv['_errors'] = $mod->get_last_error();
					}
				}
				else {
					if ($this->tv['id'] = $mod->{'add_'.$this->_table}($this->tv)) {
						//add links
						$roles_ids_arr = explode(";", substr($this->tv['twolists_right_roles_idx'], 0, strlen($this->tv['twolists_right_roles_idx']) - 1));
						if (strlen($this->tv['twolists_right_roles_idx']) > 0) {
							foreach ($roles_ids_arr as $user_role_id) {
								$this->Application->DataBase->insert_sql('user_group_user_role_link', array('user_group_id' => $this->tv['id'], 'user_role_id' => $user_role_id));
							}
						}
						$this->tv['_info'] = $this->Localizer->get_string('object_added');
						$this->tv['_return_info'] =  $this->Application->Navi->getUri('parent', false);
					}
					else {
						$this->tv['_errors'] = $mod->get_last_error();
					}
				}
			}
			else {
				$this->tv['_errors'] = CValidator::get_errors();
			}
		}
	}

	function parse_data()
	{
		parent::parse_data();
	}
}
?>