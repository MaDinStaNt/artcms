<?php
require_once(CUSTOM_CLASSES_PATH . 'admin/masterpage.php');

class CStringsPage extends CMasterPage
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $_table = 'loc_string';

    /**
     * The columns array.
     *
     * @var array
     */
    protected $_columns_arr = array('title' => 'Title');

    function CStringsPage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterPage($app, $template);
		$this->DataBase = &$this->Application->DataBase;
		$lang_rs = $this->Application->Localizer->get_languages();
		if($lang_rs == false) $lang_rs = new CRecordSet();
		$lang_rs->add_row(array('id' => '', 'title' => RECORDSET_FIRST_ITEM), INSERT_BEGIN);
		$this->_filters = array(
			'ls#language_id' => array(
	    		'title' => $this->Application->Localizer->get_string('language'),	
	    		'type' => FILTER_SELECT,
	    		'data' => array($lang_rs, 'id', 'title'),
	    		'condition' => CONDITION_EQUAL
	    	),
	    	'ls#name' => array(
	    		'title' => $this->Application->Localizer->get_string('name'),	
	    		'type' => FILTER_TEXT,
	    		'data' => null,
	    		'condition' => CONDITION_LIKE
	    	),
	    	'ls#value' => array(
	    		'title' => $this->Application->Localizer->get_string('value'),	
	    		'type' => FILTER_TEXT,
	    		'data' => null,
	    		'condition' => CONDITION_LIKE
	    	),
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
        $query = "SELECT ls.id as id, ll.title AS language, ls.name, ls.value
            FROM %prefix%loc_string ls LEFT JOIN %prefix%loc_lang ll ON (ls.language_id = ll.id)
            WHERE ".$this->_where;

        require_once(BASE_CLASSES_PATH . 'controls/dbnavigator.php');
        $nav = new DBNavigator($this->_table, $query, array('language', 'name', 'value'), 'id');
        $nav->title = 'Localizer strings';
        
        $header_num = $nav->add_header('language');
        $nav->headers[$header_num]->set_title('language');
        $nav->headers[$header_num]->set_width('10%');

        $header_num = $nav->add_header('name');
        $nav->headers[$header_num]->set_title('Name');
        $nav->headers[$header_num]->set_width('20%');

        $header_num = $nav->add_header('value');
        $nav->headers[$header_num]->set_title('Value');
        $nav->headers[$header_num]->set_width('70%');
		
        if($nav->size > 0)
        	$this->tv['remove_btn_show'] = true;
        else
        	$this->tv['remove_btn_show'] = false;
	}
}
?>