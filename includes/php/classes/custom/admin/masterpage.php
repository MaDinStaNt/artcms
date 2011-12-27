<?php
require_once(BASE_CLASSES_PATH . 'adminpage.php');
define('FILTER_TEXT', 1);
define('FILTER_SELECT', 2);
define('FILTER_DATE', 3);
define('FILTER_ENUM', 4);

define('CONDITION_EQUAL', 1);
define('CONDITION_LIKE', 2);

class CMasterPage extends CAdminPage
{
    /**
     * Default navigator columns.
     *
     * @var array
     */
    protected $_columns_arr = array('title' => 'Title');

    /**
     * Default sort column.
     *
     * @var array
     */
    protected $_sort_column = 'id';
    
    /**
     * Default where condition.
     *
     * @var array
     */
    protected $_where = '1=1';
    
    /**
     * Default filters array.
     *
     * @var array
     */
    protected $_filters = array();

    function CMasterPage(&$app, $template)
	{
		parent::CAdminPage($app, $template);
		$this->DataBase = &$this->Application->DataBase;
		$this->Localizer = &$this->Application->Localizer;
		$this->User = &$this->Application->User;
		$this->template_vars = &$app->template_vars;
	}
	
	function on_page_init()
	{
		parent::on_page_init();
		$this->tv['_table'] = $this->_table;
		require_once(BASE_CONTROLS_PATH .'adminfilters.php');
		new AdminFilters($this->_filters, $this->_table);
		global $_where;
		$this->_where = $_where[$this->_table];
	}
	
	function page_actions() {
		if (CForm::is_submit($this->_table, 'add')) {
			$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('./'.$this->_table.'_edit/', false));
		}

		if (CForm::is_submit($this->_table, 'delete_selected_objects')) {
			$ids = InGetPost("{$this->_table}_res", '');
            $where="WHERE 1=1 ";
            if (strlen($ids) > 0 && $ids !== '[]') {
            	$where .= " AND id in ({$ids}) ";
                $sql = 'DELETE FROM %prefix%'.$this->_table.' ' . $where;
                if($this->Application->DataBase->select_custom_sql($sql)) {
                	$this->tv['_info'][] = $this->Application->Localizer->get_string('objects_deleted');
                } 
                else $this->tv['_errors'][] = $this->Application->Localizer->get_string('internal_error');
           } 
           else $this->tv['_info'][] = $this->Application->Localizer->get_string('noitem_selected');
		}
	}
	
	

	function bind_data()
	{
        $sort_arr = array();
        $column_width = ceil(100 / sizeof($this->_columns_arr));
        $query = "SELECT id, ";

        foreach ($this->_columns_arr as $field) {
        	if(preg_match('/^[a-zA-Z0-9_-]+#[a-zA-Z0-9_-]+$/', $field)) 
        	{
        		$fields = explode('#', $field);
        		$sort_arr[] = $fields[1];
	        	$query .= $fields[0] . " as " . $fields[1] . ", ";
        	}
        	else {
	        	$sort_arr[] = $field;
	        	$query .= $field . ", ";
        	}
        }
        $query = substr($query, 0, strlen($query) - 2);
        $query .= " FROM %prefix%".$this->_table." WHERE " . $this->_where;
        
        require_once(BASE_CLASSES_PATH . 'controls/dbnavigator.php'); // base application class
        $nav = new DBNavigator($this->_table, $query, $sort_arr, $this->_sort_column);
        $nav->title = $this->_title;
        foreach ($this->_columns_arr as $field) {
        	if(preg_match('/^[a-zA-Z0-9_-]+#[a-zA-Z0-9_-]+$/', $field)) 
        	{
        		$fields = explode('#', $field);
        		$header_num = $nav->add_header($fields[1]);
        	}
	        else $header_num = $nav->add_header($field);
	        $nav->headers[$header_num]->set_title = $this->Application->Localizer->get_string($field);
	        $nav->headers[$header_num]->set_width = "{$column_width}%";
        }

        $this->tv['clickLink'] = $this->Application->Navi->getUri('./'.$this->_table.'_edit/', true);

        if($nav->size > 0)
        	$this->tv['remove_btn_show'] = true;
        else
        	$this->tv['remove_btn_show'] = false;
	}
}

?>