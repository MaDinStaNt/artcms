<?php
require_once('control.php');

class NaviBreadcrumb extends CControl{
	public $tv;
	public $navi;
	public $cache;
	
	function NaviBreadcrumb($object_id = null)
	{
		parent::CControl('AdminBreadcrumb', $object_id);
		$this->template = BASE_CONTROLS_TEMPLATE_PATH . 'navi_breadcrumb.tpl';
		$this->navi = $this->Application->get_module('Navi');
		
		$this->find_tag();
	}
	
	function find_tag()
	{
		global $RootPath;
		$uri = $this->navi->getUri();
		$uri = str_replace($RootPath.'admin/?r=', '', $uri);
		$tag = explode('.', $uri);
		$this->write_cache($tag);
		foreach ($this->navi->ss['children'] as $arr)
		{
			if($arr['tag'] == $tag[0]){
				$this->tv['titles'][] = $arr['title'];
				$var_str = '';
				if(!empty($this->cache[$tag[0]][0])) foreach ($this->cache[$tag[0]][0] as $var => $value) $var_str .= "&{$var}={$value}";
				$this->tv['links'][] = $tag[0].$var_str;
				if(isset($arr['children']) && is_array($arr['children'])) $this->find_recursive(1, $tag, $arr['children']);
			}
		}
		$this->tv['is_current'][count($tag) - 1] = true;
	}
	
	function find_recursive($key, $tag, $ss)
	{
		if(!isset($tag[$key])) return false;
		foreach ($ss as $arr)
		{
			if($arr['tag'] == $tag[$key])
			{
				$this->tv['titles'][] = $arr['title'];
				$this->tv['links'][$key] = '';
				$this->tv['is_current'][] = false;
				for($i = 0; $i <= $key; $i++)
					$this->tv['links'][$key] .= $tag[$i].'.';
				$this->tv['links'][$key] = substr($this->tv['links'][$key], 0, -1);
				$var_str = '';
				if(!empty($this->cache[$tag[0]][$key])) foreach ($this->cache[$tag[0]][$key] as $var => $value) $var_str .= "&{$var}={$value}";
				$this->tv['links'][$key] .= $var_str;
				$key++;
				if(isset($arr['children']) && is_array($arr['children'])) $this->find_recursive($key, $tag, $arr['children']);
			}
		}
	}
	
	function write_cache($tags)
	{
		if(!isset($_SESSION['cache']['AdminBreadcrumb'])) $_SESSION['cache']['AdminBreadcrumb'] = array();
		$this->cache = &$_SESSION['cache']['AdminBreadcrumb'];
		$data = array();
		foreach($_GET as $var => $val) 
			if($var !== 'r') $data[$var] = $val;
			
		$key = count($tags) - 1;
		if(!isset($this->cache[$tags[0]])) $this->cache = array();
		$this->cache[$tags[0]][$key] = $data;
	}
}
?>