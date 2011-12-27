<?
class CControl
{
	public $tv;
	public $Application;
	public $html_page;
	public $template;
	public $object_id;

	function CControl($name, $object_id = null)
	{
		$this->Application = $GLOBALS['app'];

		if(!isset($GLOBALS['control_tv'])) $GLOBALS['control_tv'] = array();
		if(!isset($GLOBALS['control_template'])) $GLOBALS['control_template'] = array();
		if(is_null($object_id))
		{
			$GLOBALS['control_tv'][$name] = $this->Application->tv;
			$this->tv = &$GLOBALS['control_tv'][$name];
			$GLOBALS['control_template'][$name] = '';
			$this->template = &$GLOBALS['control_template'][$name];
		}
		else
		{
			$GLOBALS['control_tv'][$name][$object_id] = $this->Application->tv;
			$this->tv = &$GLOBALS['control_tv'][$name][$object_id];
			$GLOBALS['control_template'][$name][$object_id] = '';
			$this->template = &$GLOBALS['control_template'][$name][$object_id];
		}

		$this->html_page = &$this->Application->CurrentPage;
		$this->html_page->m_Controls[] = &$this;
		if(!is_null($object_id))
			$this->tv['object_id'] = $this->object_id = $object_id;
	}

	function on_page_init(){
		return true;
	}

	function process($name, $object_id = null)
	{
		if(is_null($object_id))
		{
			$c_template = $GLOBALS['control_template'][$name];
			$template_vars = $GLOBALS['control_tv'][$name];
		}
		else
		{
			$c_template = $GLOBALS['control_template'][$name][$object_id];
			$template_vars = $GLOBALS['control_tv'][$name][$object_id];
		}

		if(strlen($c_template) > 0)
			self::control_template($template_vars, $c_template);
	}
	
	function control_template($tv, $template){
		foreach ($tv as $key => $value){ ${$key} = $value; }
		if(file_exists($template)){
			include($template);
		}
		else
			system_die("Control Invalid template path {$template}");
	}
};
?>