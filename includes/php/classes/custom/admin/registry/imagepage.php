<?php
require_once(CUSTOM_CLASSES_PATH . 'admin/masterpage.php');

class CImagePage extends CMasterPage
{
    /**
     * The table name.
     *
     * @var array
     */
    protected $_table = 'image';
    /**
     * The table name.
     *
     * @var array
     */
    protected $_filters = array();

    /**
     * The columns array.
     *
     * @var array
     */
    
    protected $_title = 'Images';
    
    protected $_columns_arr = array('title');

	function CImagePage(&$app, $template)
	{
		$this->IsSecure = true;
		parent::CMasterPage($app, $template);
		$this->DataBase = &$this->Application->DataBase;
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
}
?>