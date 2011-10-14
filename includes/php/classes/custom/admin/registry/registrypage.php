<?php
require_once(BASE_CLASSES_PATH . 'adminpage.php');

class CRegistryPage extends CAdminPage
{
    function CRegistryPage(&$app, $template)
	{
		$this->IsSecure = true;
		$this->Application = &$app;
		$this->Registry = &$this->Application->get_module('Registry');
		$this->tv = &$this->Application->tv;
		parent::CAdminPage($app, $template);
		$this->tv['registry_path'] = REGISTRY_FILES_WEB;
	}
	
	function on_page_init()
	{
		parent::on_page_init();
		$this->bind_path();
		$this->bind_tree();
	}
	
	function parse_data()
	{
        if (!parent::parse_data()) return false;
        
        //$this->tv['action_form'] = substr($_SERVER['REQUEST_URI'], 1, strlen($_SERVER['REQUEST_URI']));
        return true;
	}
	
	function bind_path()
	{
		$path_id = $this->tv['path_id'] = (int)InGetPost('path_id', false);
		if($path_id)
		{
			$path_rs = $this->Application->DataBase->select_custom_sql('
				SELECT
					rv.id as id,
					rv.path_id as path_id,
					rv.path as path,
					rv.type as type,
					rv.required as required,
					rv.title as title,
					rv.value as value,
					rm.type as valid_type,
					rm.unique_error_mess as valid_uem,
					rm.add_info1 as valid_add_info1,
					rm.add_info2 as valid_add_info2,
					rm.add_info3 as valid_add_info3
				FROM registry_value rv left join registry_meta rm on ((rv.id = rm.value_id))
				WHERE rv.path_id = '.$path_id.'
			');
			if($path_rs !== false && !$path_rs->eof())
			{
				$values_arr = &$this->tv['values_arr'];
				$values_arr = array();
				while (!$path_rs->eof())
				{
					$values_arr[$path_rs->get_current_row()]['id'] = $path_rs->get_field('id');
					$values_arr[$path_rs->get_current_row()]['path'] = "registry_path_value_#{$path_rs->get_field('id')}___{$path_rs->get_field('path')}";
					$values_arr[$path_rs->get_current_row()]['type'] = $path_rs->get_field('type');
					$values_arr[$path_rs->get_current_row()]['required'] = $path_rs->get_field('required');
					$values_arr[$path_rs->get_current_row()]['title'] = $path_rs->get_field('title');
					$this->tv["registry_path_value_#{$path_rs->get_field('id')}___{$path_rs->get_field('path')}"] = $path_rs->get_field('value');
					if(intval($path_rs->get_field('valid_type')) > 0)
						if(intval($path_rs->get_field('required')) > 0)
							CValidator::add("registry_path_value_#{$path_rs->get_field('id')}___{$path_rs->get_field('path')}", $path_rs->get_field('valid_type'), $path_rs->get_field('valid_uem'), $path_rs->get_field('valid_add_info1'), $path_rs->get_field('valid_add_info2'), $path_rs->get_field('valid_add_info3'));
						else 
							CValidator::add_nr("registry_path_value_#{$path_rs->get_field('id')}___{$path_rs->get_field('path')}", $path_rs->get_field('valid_type'), $path_rs->get_field('valid_uem'), $path_rs->get_field('valid_add_info1'), $path_rs->get_field('valid_add_info2'), $path_rs->get_field('valid_add_info3'));
					$path_rs->next();
				}
			}
			else $this->tv['reg_info'] = $this->Application->Localizer->get_string('values_not_found');
		}
	}
	
	function bind_tree()
	{
		$language_id = $this->tv['registry_language_id'] = (int)InGet('lang_id', $this->tv['language_id']);
		$deep_tree = $this->Registry->get_deep_tree($language_id);
        if($deep_tree)
        {
        	if($deep_tree > 0)
        	{
        		$sql = "SELECT 
        			rp1.id as id, rp1.parent_id as parent_id, rp1.path as path, rp1.title as title, rp2.id as children_id
        		FROM registry_path rp1 left join registry_path rp2 on ((rp2.parent_id = rp1.id and rp1.language_id = rp2.language_id))
        		WHERE
        			rp1.parent_id >= 0
        		ORDER by rp1.parent_id ASC";
        		$tree_rs = $this->Application->DataBase->select_custom_sql($sql);
        		if($tree_rs !== false && !$tree_rs->eof())
        		{
        			$tree_arr = array();
        			$i = 0;
        			while (!$tree_rs->eof())
        			{
        				if($tree_rs->get_field('parent_id') == 0)
        				{
        					if(array_search_assoc($tree_arr, array('id' => $tree_rs->get_field('id'))) === false)
        					{
        						$tree_arr[$i]['id'] = $tree_rs->get_field('id');
        						$tree_arr[$i]['path'] = $tree_rs->get_field('path');
        						$tree_arr[$i]['title'] = $tree_rs->get_field('title');
        						if(intval($tree_rs->get_field('children_id')) > 0) $tree_arr[$i]['children'] = array();
        						else $tree_arr[$i]['children'] = false;
        						$i++;
        					}
        				}
        				else $this->tree_children($tree_arr, $tree_rs);
        				$tree_rs->next();
        			}
        			$this->build_tree($tree_arr);
        		}
        		else $this->tv['_info'] = $this->Application->Localizer->get_string('registry_is_empty');
        	}
        	else $this->tv['_info'] = $this->Application->Localizer->get_string('registry_is_empty');
        }
        else $this->tv['_errors'] = $this->Registry->get_last_error();
	}
	
	function tree_children(&$tree_arr, &$tree_rs)
	{
		$key = array_search_assoc($tree_arr, array('id' => $tree_rs->get_field('parent_id')));
		if($key !== false)
		{
			if(is_array($tree_arr[$key]['children']))
			{
				$cnt_children = count($tree_arr[$key]['children']);
				$tree_arr[$key]['children'][$cnt_children]['id'] = $tree_rs->get_field('id');
				$tree_arr[$key]['children'][$cnt_children]['path'] = $tree_rs->get_field('path');
				$tree_arr[$key]['children'][$cnt_children]['title'] = $tree_rs->get_field('title');
				if(intval($tree_rs->get_field('children_id')) > 0) $tree_arr[$key]['children'][$cnt_children]['children'] = array();
        		else $tree_arr[$key]['children'][$cnt_children]['children'] = false;
			}
			else $this->tv['_info'] = $this->Application->Localizer->get_string('internal_error');
		}
		else foreach ($tree_arr as $key => $arr) $this->tree_children($tree_arr[$key]['children'], $tree_rs); 
	}
	
	function build_tree($tree_arr)
	{
		global $DebugLevel;
		$path_id = InGetPost('path_id', false);
		$tree = &$this->tv['tree'];
		$tree .= '<ul class="branch">';
		foreach ($tree_arr as $arr)
		{
			$tree .= '<li class="'.((empty($arr['children'])) ? 'list' : null).(($path_id == $arr['id']) ? ' active expand' : null). '">';
			$tree .= '
				<span class="marker">&nbsp;</span>
				<a href="'.$this->Application->Navi->getUri().'&path_id='.$arr['id'].'" title="'.$this->Application->Localizer->get_string('goto').' \''.$arr['title'].'\'" '.(($path_id == $arr['id']) ? 'class="title"' : null).'>'.$arr['title'].'</a>';
			if($DebugLevel > 0)
				$tree .= '<a href="'.$this->Application->Navi->getUri().'.path_edit&id='.$arr['id'].'" class="edit"><span class="inv">edit</span></a>
					<a href="#" class="delete"><span class="inv">delete</span></a>';
			if(!empty($arr['children'])) $this->build_tree($arr['children']);
			$tree .= '</li>';
		}
		$tree .= '</ul>';
		
	}
	
	function on_registry_submit($action)
	{
		switch ($action){
			case 'add_path':
				if(CForm::is_submit('registry', 'add_path'))
				{
					$this->Application->CurrentPage->internalRedirect($this->Application->Navi->getUri('./path_edit/'));
				}
			break;
			case 'save_path':
				if(CForm::is_submit('registry', 'save_path'))
				{
					if(CValidator::validate_input())
					{
						$path_id = InGetPost('path_id', false);
						if($path_id)
							if($this->Registry->update_value($path_id, $this->tv))
								$this->tv['reg_info'] = $this->Application->Localizer->get_string('object_updated');
							else 
								$this->tv['reg_errors'] = $this->Registry->get_last_error();
					}
					else $this->tv['reg_errors'] = CValidator::get_errors();
				}
			break;
		}
		
	    $this->bind_path();
	}
}
?>