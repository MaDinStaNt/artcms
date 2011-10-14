<?php
require_once('control.php');

class CNavi extends CControl 
{
	function CNavi($name, $object_id)
	{
		parent::CControl($name, $object_id);
		$this->template = BASE_CONTROLS_TEMPLATE_PATH . 'navi.tpl';
		$this->bind_data();
	}
	
	function bind_data()
	{
		global $RootPath, $navi_result;
		$link_part = $RootPath . 'admin/?r=';
		$navi = $this->Application->get_module('Navi');
		$separator = $navi->requestSeparator;
		$menu_arr = $navi->ss['children'];
		$links_arr = array();
		$uri = $navi->getUri();
		$uri = str_replace($RootPath.'admin/?r=', '', $uri);
		$tag = explode('.', $uri);
		foreach ($menu_arr as $ma_key => $ma_value)
		{
			if(isset($ma_value['mode']) && $ma_value['mode'] == 'hidden') continue;
			
			$links_arr[$ma_key]['link'] = $link_part.$ma_value['tag'];
			$links_arr[$ma_key]['name'] = $ma_value['title'];
			$links_arr[$ma_key]['is_act'] = ($tag[0] == $ma_value['tag']);
			$sub_menu_arr = $ma_value['children'];
			foreach ($sub_menu_arr as $s_key => $s_value)
			{
				if(isset($s_value['mode']) && $s_value['mode'] == 'hidden') continue;
				
				$links_arr[$ma_key]['children'][$s_key]['link'] = $links_arr[$ma_key]['link'].$separator.$s_value['tag'];
				$links_arr[$ma_key]['children'][$s_key]['name'] = $s_value['title'];
				$links_arr[$ma_key]['children'][$s_key]['is_act'] = ($tag[1] == $s_value['tag']);
				$sub_menu2_arr = $s_value['children'];
				foreach ($sub_menu2_arr as $s2_key => $s2_value)
				{
					if(isset($s2_value['mode']) && $s2_value['mode'] == 'hidden') continue;
					
					$links_arr[$ma_key]['children'][$s_key]['children'][$s2_key]['link'] = $links_arr[$ma_key]['children'][$s_key]['link'].$separator.$s2_value['tag'];
					$links_arr[$ma_key]['children'][$s_key]['children'][$s2_key]['name'] = $s2_value['title'];
					$links_arr[$ma_key]['children'][$s_key]['children'][$s2_key]['is_act'] = ($tag[2] == $s2_value['tag']);
				}
				
			}
			
		}
		
		//print_arr($links_arr);
		if(!empty($links_arr)) $this->tv['navi'] = $links_arr;
		else $this->tv['navi_is_empty'] = true;
	}
}
?>