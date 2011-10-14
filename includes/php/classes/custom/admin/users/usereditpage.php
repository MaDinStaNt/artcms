<?
require_once(CUSTOM_CLASSES_PATH . 'admin/mastereditpage.php');

class CUserEditPage extends CMasterEditPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'user';
    
    protected $_module = 'User';

    function CUserEditPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterEditPage($app, $template);
		CInput::set_select_data('state_id', $this->User->get_states(), 'id', 'title');
		CInput::set_select_data('user_role_id', $this->User->get_user_roles(), 'id', 'title');
		CInput::set_select_data('status', $this->get_user_status_array());
	}

	function on_page_init()
	{
		parent::on_page_init();
		CValidator::add('email', VRT_EMAIL, 0, 255);
		if ($this->id)
			CValidator::add_nr('password', VRT_PASSWORD, false, 0, 255);
		else
			CValidator::add('password', VRT_PASSWORD, false, 0, 255);
		CValidator::add('name', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('address', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('city', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('state_id', VRT_TEXT, false, 0, 255);
		CValidator::add_nr('zip', VRT_US_ZIP, false);
	}

	
	function parse_data()
	{
		parent::parse_data();
	}
}
?>