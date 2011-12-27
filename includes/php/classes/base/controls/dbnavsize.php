<?php
require_once('control.php');

class DBNavSize extends CControl{
	
	public $size;
	public $sizes = array(10, 50, 100);
	protected $getpost_var;
	protected $default_size = 10;
	
	function DBNavSize($dbnav_object_id = null)
	{
		parent::CControl('DBNavSize', $dbnav_object_id);
		$this->template = BASE_CONTROLS_TEMPLATE_PATH . 'dbnav_size.tpl';
		$this->navi = $this->Application->get_module('Navi');
		$this->getpost_var = "dbnav_{$this->object_id}_size";
	}
	
	function bind_data()
	{
		$this->size = InGetPostCache($this->getpost_var, false);
		if(intval($this->size) < 1)
			$this->size = InCookie($this->getpost_var, false);
			
		if(intval($this->size) < 1 || !in_array($this->size, $this->sizes))
			$this->size = $this->default_size;
			
		SetCacheVar($this->getpost_var, $this->size);
		setcookie($this->getpost_var, $this->size, time() + 60*60*24*31, '/');
		$this->tv['size_act'] = $this->size;
		$this->tv['link'] = $this->Application->Navi->getUri();
		foreach ($_GET as $varname => $value)
			if($varname !== 'r' && $varname !== $this->getpost_var)
				$this->tv['link'] .= "&{$varname}={$value}";
				
		$this->tv['link'] .= "&{$this->getpost_var}=";
		$this->tv['sizes'] = $this->sizes;		
	}
	
	function get_size()
	{
		return $this->size;
	}
	
	function set_sizes($sizes_arr)
	{
		if(!is_array($sizes_arr))
			system_die('DBNavSize -> set_sizes: invalid array $sizes_arr');
		else 
			$this->sizes = $sizes_arr;
	}
}
?>