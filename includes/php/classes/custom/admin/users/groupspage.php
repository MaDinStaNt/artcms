<?php
require_once(CUSTOM_CLASSES_PATH . 'admin/masterpage.php');

class CGroupsPage extends CMasterPage
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $_table = 'user_group';

    /**
     * The columns array.
     *
     * @var array
     */
    protected $_columns_arr = array();

    function CGroupsPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterPage($app, $template);
		$this->DataBase = &$this->Application->DataBase;
	}
	
	function on_page_init()
	{
		parent::on_page_init();
		parent::page_actions();
		parent::page_filters();
	}
	
	function parse_data()
	{
		if (!parent::parse_data()) return false;
		
		$this->bind_data();
		
        return true;
	}
	
	function bind_data()
	{
        $query = "SELECT *
            FROM %prefix%user_group
            WHERE ".$this->_where;

        require_once(BASE_CLASSES_PATH . 'controls/navigator.php'); // base application class
        $nav = new Navigator('objects', $query, array('title' => 'title', 'description' => 'description'), 'title', $this->Application);
        
        $header_num = $nav->add_header($this->Application->Localizer->get_string('title'), 'title');
        $nav->headers[$header_num]->no_escape = false;
        $nav->headers[$header_num]->set_wrap();
        $nav->set_width($header_num, '50%');

        $header_num = $nav->add_header($this->Application->Localizer->get_string('description'), 'description');
        $nav->headers[$header_num]->no_escape = false;
        $nav->headers[$header_num]->set_wrap();
        $nav->set_width($header_num, '50%');

        $groups = array(1, 2, 3, 4);
        $nav->set_disabled_list($groups);
        $this->tv['clickLink'] = $this->Application->Navi->getUri('./'.$this->_table.'_edit/', true);

        if ($nav->size > 1)
            $this->template_vars[$this->_table.'_show_remove'] = true;
        else
            $this->template_vars[$this->_table.'_show_remove'] = false;

	}
}
?>