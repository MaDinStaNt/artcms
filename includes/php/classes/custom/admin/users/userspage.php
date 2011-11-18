<?php
require_once(CUSTOM_CLASSES_PATH . 'admin/masterpage.php');

class CUsersPage extends CMasterPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'user';
    /**
     * The table name.
     *
     * @var array
     */
    protected $_filters = array();

	function CUsersPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterPage($app, $template);
		$this->DataBase = &$this->Application->DataBase;
		$roles_rs = $this->User->get_user_roles();
		$roles_rs->add_row(array('id' => '', 'title' => $this->Application->Localizer->get_string('any')), INSERT_BEGIN);
	    $this->_filters = array(
	    	'm#name' => array(
	    		'title' => $this->Application->Localizer->get_string('name'),	
	    		'type' => FILTER_TEXT,
	    		'data' => null,
	    		'condition' => CONDITION_LIKE
	    	),
	    	'm#company' => array(
	    		'title' => $this->Application->Localizer->get_string('company'),	
	    		'type' => FILTER_TEXT,
	    		'data' => null,
	    		'condition' => CONDITION_LIKE
	    	),
	    	'm#email' => array(
	    		'title' => $this->Application->Localizer->get_string('email'),	
	    		'type' => FILTER_TEXT,
	    		'data' => null,
	    		'condition' => CONDITION_LIKE
	    	),
	    	'm#user_role_id' => array(
	    		'title' => $this->Application->Localizer->get_string('user_role_id'),	
	    		'type' => FILTER_SELECT,
	    		'data' => array($roles_rs, 'id', 'title'),
	    		'condition' => CONDITION_EQUAL
	    	),
	    	'm#create_date' => array(
	    		'title' => $this->Application->Localizer->get_string('create_date_from'),	
	    		'type' => FILTER_DATE,
	    	)
	    );
	}
	
	function on_page_init()
	{
		parent::on_page_init();
		parent::page_actions();
	}
	
	function parse_data()
	{
		if (!parent::parse_data()) return false;
		$this->bind_data();
        return true;
	}
	
	function bind_data()
	{
        $query = "SELECT m.id as id,
            CASE m.status WHEN 1 THEN '<img src=\"".$this->tv['HTTP']."images/icons/user.gif\">' ELSE '<img src=\"".$this->tv['HTTP']."images/icons/user_.gif\">' END AS status,
            m.name AS name,
            m.company AS company,
            CASE m.email WHEN '' THEN '-' ELSE m.email END AS email,
            ml.title AS user_role,
            DATE_FORMAT(m.create_date, '%b %d, %Y %h:%i %p') AS create_date_formatted
            FROM %prefix%user AS m LEFT JOIN %prefix%user_role ml ON (m.user_role_id = ml.id)
            WHERE ".$this->_where."";
		
        require_once(BASE_CLASSES_PATH . 'controls/dbnavigator.php');
        $nav = new DBNavigator('users', $query, array('status', 'company', 'name', 'email'), 'id');
        $nav->title = 'Users List';
        
        $header_num = $nav->add_header('status');
        $nav->headers[$header_num]->set_title('status');
        $nav->headers[$header_num]->set_align( "center");
        
        $header_num = $nav->add_header('name');
        $nav->headers[$header_num]->set_title('Name');
        $nav->headers[$header_num]->set_width( "20%");
        
        $header_num = $nav->add_header('company');
        $nav->headers[$header_num]->set_title('Company');
        $nav->headers[$header_num]->set_width( "20%");
        
        $header_num = $nav->add_header('email');
        $nav->headers[$header_num]->set_title('email');
        $nav->headers[$header_num]->set_width( "20%");
        $nav->headers[$header_num]->set_mail(true);
        
        $header_num = $nav->add_header('user_role');
        $nav->headers[$header_num]->set_title('User Role');
        $nav->headers[$header_num]->set_width( "20%");
        
        $header_num = $nav->add_header('create_date_formatted');
        $nav->headers[$header_num]->set_title('Create Date');
        $nav->headers[$header_num]->set_width( "20%");
        
        if($nav->size > 0)
        	$this->tv['remove_btn_show'] = true;
        else
        	$this->tv['remove_btn_show'] = false;
        
        //print_arr($GLOBALS['control_tv']);
        $query = "SELECT id, title, position FROM position";
        
        $nav = new DBNavigator('pos', $query, array(), 'position');
        $nav->_table = 'position';
        $header_num = $nav->add_header($this->Application->Localizer->get_string('title'), 'title');
        $nav->headers[$header_num]->no_escape = true;
        $nav->headers[$header_num]->align = "center";
        $nav->headers[$header_num]->set_width('99%');
        $nav->headers[$header_num]->set_wrap(true);

        $header_num = $nav->add_header($this->Application->Localizer->get_string('position'), 'position');
        $nav->headers[$header_num]->no_escape = false;
        $nav->headers[$header_num]->set_wrap();
        $nav->headers[$header_num]->set_width('5%');
        $nav->headers[$header_num]->set_position();
        /*$parent_arr = array( '' => RECORDSET_FIRST_ITEM, 1 => 'first parent', 2 => 'second parent', 3 => 'third parent');
        $category_arr = array( '' => RECORDSET_FIRST_ITEM, 1 => 'first category', 2 => 'second category', 3 => 'third category');
        $nav->swapPosition("SELECT id, title, parent_id, category_id, position, pos_category FROM position",
        	array('parent_id' => 'Parent', 'category_id' => 'Category'), 
        	array('parent_id' => $parent_arr, 'category_id' => $category_arr), 
        	array('parent_id' => 'position', 'category_id' => 'pos_category')
        );*/
        $nav->swapPosition("SELECT id, title, parent_id, category_id, position, pos_category FROM position");
        
        
        //print_arr($GLOBALS['control_tv']);
	}
}
?>