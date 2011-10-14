<?php
define('KEY_TYPE_TEXT', 1);
define('KEY_TYPE_TEXTAREA', 2);
define('KEY_TYPE_HTML', 3);
define('KEY_TYPE_FILE', 4);
define('KEY_TYPE_DATE', 5);
define('KEY_TYPE_CHECKBOX', 6);
define('KEY_TYPE_IMAGE', 7);

class CRegistry{
	
	protected $Application;
	protected $DataBase;
	protected $tv;
	protected $last_error;
	
	function CRegistry(&$app)
	{
		$this->Application = &$app;
		$this->DataBase = &$this->Application->DataBase;
		$this->tv = &$app->tv; 	
	}
	
	function get_last_error()
	{
		return $this->last_error;
	}
	
	function get_pathes($lang_id = null)
	{
		if(is_null($lang_id)) $rs = $this->DataBase->select_custom_sql('SELECT * FROM registry_path WHERE parent_id >= 0');
		else $rs = $this->DataBase->select_sql('registry_path', array('language_id' => $lang_id));
		if($rs == false || $rs->eof()) return false;
		return $rs;
	}
	
	function get_pathes_values($pathes, $lang_id = null)
	{
		if(is_null($lang_id)) $lang_id = $this->tv['language_id'];
		if(!is_numeric($lang_id) || !is_string($pathes))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$sql ="
        	SELECT
        		rp.path as path,
        		rv.path as value_path,
        		rv.value as value
        	FROM
        		registry_path rp join registry_value rv on ((rv.path_id = rp.id))
        	WHERE
        ";
		
		$path_arr = explode('/', $pathes);
		$where = ' rp.language_id = '.$lang_id.' AND (1=2';
		$order = ' ORDER by rp.id ASC';
		foreach ($path_arr as $path) $where .= ' OR rp.path="'. $path .'"';
		$where .= ')';
		$registry_rs = $this->DataBase->select_custom_sql($sql.$where.$order);
		if($registry_rs == false || $registry_rs->eof()) return false;
		
		return $registry_rs;
	}
	
	function add_path($arr)
	{
		if(!is_array($arr))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$check_rs = $this->DataBase->select_sql('SELECT count(id) as cnt FROM registry_path WHERE path = "'.mysql_escape_string($arr['path']).'" OR title = "'.mysql_escape_string($arr['path']).'"');
		if($check_rs == false)
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		if($check_rs->get_field('cnt') > 0)
		{
			$this->last_error = $this->Application->Localizer->get_string('object_exists');
			return false;
		}
		
		$insert_arr = array(
			'language_id' => $arr['language_id'],
			'parent_id' => intval($arr['parent_id']),
			'path' => $arr['path'],
			'title'=> $arr['title']
		);
		
		if(!$id = $this->DataBase->insert_sql('registry_path', $insert_arr))
		{
			$this->last_error = $this->Application->Localizer->get_string('database_error');
			return false;
		}
		
		if(!$this->check_depth($arr['language_id'])) return false;
		
		return $id;
	}
	
	function update_path($id, $arr)
	{
		if(!is_array($arr) || !is_numeric($id))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$check_rs = $this->DataBase->select_sql('SELECT count(id) as cnt FROM registry_path WHERE (path = "'.mysql_escape_string($arr['path']).'" OR title = "'.mysql_escape_string($arr['path']).'") AND id <>'.$id);
		if($check_rs == false)
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		if($check_rs->get_field('cnt') > 0)
		{
			$this->last_error = $this->Application->Localizer->get_string('object_exists');
			return false;
		}
		
		$update_arr = array(
			'language_id' => $arr['language_id'],
			'parent_id' => intval($arr['parent_id']),
			'path' => $arr['path'],
			'title'=> $arr['title']
		);
		
		if(!$this->DataBase->update_sql('registry_path', $update_arr, array('id' => $id)))
		{
			$this->last_error = $this->Application->Localizer->get_string('database_error');
			return false;
		}
		
		return $id;
	}
	
	function add_registry_value($arr)
	{
		if(!is_array($arr))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$check_rs = $this->DataBase->select_sql('SELECT count(id) as cnt FROM registry_value WHERE (path = "'.mysql_escape_string($arr['path']).'" OR title = "'.mysql_escape_string($arr['path']).'") and path_id ='.$arr['path_id']);
		if($check_rs == false)
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		if($check_rs->get_field('cnt') > 0)
		{
			$this->last_error = $this->Application->Localizer->get_string('object_exists');
			return false;
		}
		
		$insert_arr = array(
			'path_id' => $arr['path_id'],
			'type' => intval($arr['type']),
			'path' => $arr['path'],
			'required' => intval($arr['required']),
			'title'=> $arr['title'],
			'value'=> $arr['value']
		);
		
		if(!$id = $this->DataBase->insert_sql('registry_value', $insert_arr))
		{
			$this->last_error = $this->Application->Localizer->get_string('database_error');
			return false;
		}
		
		if(intval($arr['valid_type']) > 0)
		{
			$insert_arr = array(
				'value_id' => $id,
				'type' => $arr['valid_type'],
				'unique_error_mess' => intval($arr['unique_error_mess']),
				'add_info1' => $arr['valid_add_info1'],
				'add_info2' => $arr['valid_add_info2'],
				'add_info3' => $arr['valid_add_info3']
			);
			$this->DataBase->insert_sql('registry_meta', $insert_arr);
		}
		
		return $id;
	}
	
	function update_registry_value($id, $arr)
	{
		if(!is_array($arr) || !is_numeric($id))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$check_rs = $this->DataBase->select_sql('SELECT count(id) as cnt FROM registry_value WHERE (path = "'.mysql_escape_string($arr['path']).'" OR title = "'.mysql_escape_string($arr['path']).'") and path_id ='.$arr['path_id'].' and id <> '.$id);
		if($check_rs == false)
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		if($check_rs->get_field('cnt') > 0)
		{
			$this->last_error = $this->Application->Localizer->get_string('object_exists');
			return false;
		}
		
		$update_arr = array(
			'path_id' => $arr['path_id'],
			'type' => intval($arr['type']),
			'path' => $arr['path'],
			'required' => intval($arr['required']),
			'title'=> $arr['title'],
			'value'=> $arr['value']
		);
		
		if(!$this->DataBase->update_sql('registry_value', $update_arr, array('id' => $id)))
		{
			$this->last_error = $this->Application->Localizer->get_string('database_error');
			return false;
		}

		if(intval($arr['valid_type']) > 0)
		{
			$meta_rs = $this->DataBase->select_sql('registry_meta', array('value_id' => $id));
			if($meta_rs == false)
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
			elseif (!$meta_rs->eof())
			{
				$update_arr = array(
					'type' => $arr['valid_type'],
					'unique_error_mess' => intval($arr['unique_error_mess']),
					'add_info1' => $arr['valid_add_info1'],
					'add_info2' => $arr['valid_add_info2'],
					'add_info3' => $arr['valid_add_info3']
				);
				if(!$this->DataBase->update_sql('registry_meta', $update_arr, array('value_id' => $id)))
				{
					$this->last_error = $this->Application->Localizer->get_string('database_error');
					return false;
				}
				
			}
			else {
				$insert_arr = array(
					'value_id' => $id,
					'type' => $arr['valid_type'],
					'unique_error_mess' => intval($arr['unique_error_mess']),
					'add_info1' => $arr['valid_add_info1'],
					'add_info2' => $arr['valid_add_info2'],
					'add_info3' => $arr['valid_add_info3']
				);
				$this->DataBase->insert_sql('registry_meta', $insert_arr);
			}
		}
		else 
		{
			if(!$this->DataBase->delete_sql('registry_meta', array('value_id' => $id)))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
		}
		
		return $id;
	}
	
	function check_depth($language_id)
	{
		if(!is_numeric($language_id))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$check_rs = $this->DataBase->select_sql('registry_path', array('language_id' => $language_id, 'parent_id' => -1));
		if($check_rs == false)
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		if($check_rs->eof())
		{
			$insert_arr = array(
				'parent_id' => -1,
				'language_id' => $language_id,
				'path' => '_depth_tree',
				'title' => 'Depth of the Registry tree'
			); 
			
			if(!$id = $this->DataBase->insert_sql('registry_path', $insert_arr))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
			
			$insert_arr = array(
				'path_id' => $id,
				'path' => '_count',
				'type' => KEY_TYPE_TEXT,
				'title' => 'Count',
				'value' => 0
			);
			
			if(!$id = $this->DataBase->insert_sql('registry_value', $insert_arr))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
			
			$count_rs = new CRecordSet();
			$count_rs->add_field('id');
			$count_rs->add_field('value');
			$count_rs->add_row(array('id' => $id, 'value' => 0));
		}
		else 
		{
			$count_rs = $this->DataBase->select_sql('registry_value', array('path_id' => $check_rs->get_field('id')));
			if($count_rs == false)
			{
				$this->last_error = $this->Application->Localizer->get_string('internal_error');
				return false;
			}
			if($count_rs->eof())
			{
				$insert_arr = array(
					'path_id' => $id,
					'path' => '_count',
					'type' => KEY_TYPE_TEXT,
					'title' => 'Count',
					'value' => 0
				);
				
				if(!$id = $this->DataBase->insert_sql('registry_value', $insert_arr))
				{
					$this->last_error = $this->Application->Localizer->get_string('database_error');
					return false;
				}
				
				$count_rs = new CRecordSet();
				$count_rs->add_field('id');
				$count_rs->add_field('value');
				$count_rs->add_row(array('id' => $id, 'value' => 0));
			}
		}
		
		$depth = 0;
		$depth_rs = $this->DataBase->select_sql('registry_path', array('parent_id' => 0));
		while (!$depth_rs->eof())
		{
			$sql = "SELECT rp1.id FROM registry_path rp1";
			for($i = 0; $i < $depth; $i++) $sql .= " join registry_path rp".($i + 2)." on((rp".($i + 2).".parent_id = rp".($i + 1).".id))";
			$depth_rs = $this->DataBase->select_custom_sql($sql);
			if($depth_rs !== false && !$depth_rs->eof) $depth = $i + 1;
			else break;
		}
		
		$depth--;
		
		if($count_rs->get_field('value') !== $depth)
			if(!$this->DataBase->update_sql('registry_value', array('value' => $depth), array('id' => $count_rs->get_field('id')))){
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
			
		return true;
	}
	
	function get_deep_tree($language_id)
	{
		if(!is_numeric($language_id))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$deep_tree_rs = $this->DataBase->select_custom_sql('SELECT rv.value as count FROM registry_path rp join registry_value rv on ((rv.path_id = rp.id)) WHERE rp.parent_id = "-1" AND rp.language_id = '.$language_id.' AND rp.path="_depth_tree"');
		if($deep_tree_rs == false)
		{
			$this->last_error = $this->Application->Localizer->get_string('database_error');
			return false;
		}
		if($deep_tree_rs->eof() || !is_numeric($deep_tree_rs->get_field('count')))
		{
			$this->check_depth($language_id);
			$deep_tree_rs = $this->DataBase->select_custom_sql('SELECT rv.value as count FROM registry_path rp join registry_value rv on ((rv.path_id = rp.id)) WHERE rp.parent_id = "-1" AND rp.language_id = '.$language_id.' path="_depth_tree"');
			if($deep_tree_rs == false)
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
		}
		
		if(!is_numeric($deep_tree_rs->get_field('count'))) return false;
		
		return $deep_tree_rs->get_field('count');
	}
	
	function update_value($path_id, $arr)
	{
		if(!is_numeric($path_id) || !is_array($arr))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		$check_rs = $this->DataBase->select_sql('registry_path', array('id' => $path_id));
		if($check_rs == false || $check_rs->eof())
		{
			$this->last_error = $this->Application->Localizer->get_string('internal_error');
			return false;
		}
		
		foreach ($arr as $name => $value)
			if(preg_match('/^registry_path_value_#[0-9]+___[0-9a-z_-]+$/iu', $name))
			{
				$names = str_replace('registry_path_value_#', '', $name);
				$names = explode('___', $names);
				$value_id = $names[0];
				$value_path = $names[1];
				$update_value = $value;
				
				$check_rs = $this->DataBase->select_sql('registry_value', array('id' => $value_id, 'path' => $value_path));
				if($check_rs == false || $check_rs->eof()) continue;
				if((intval($check_rs->get_field('type')) == KEY_TYPE_FILE || intval($check_rs->get_field('type')) == KEY_TYPE_IMAGE))
				{
					$update_value = $check_rs->get_field('value');
				}
				
				if(intval($arr["delete_{$name}"]) > 0)
					if(file_exists(ROOT.REGISTRY_FILES_WEB.$update_value))
					{ 
						@unlink(ROOT.REGISTRY_FILES_WEB.$update_value);
						$update_value = '';
					}
				
				if(isset($_FILES[$name]) && strlen($this->tv[$name.'_file_content']) > 0)
				{
					if(!file_exists(ROOT.REGISTRY_FILES_WEB))
						@mkdir(ROOT.REGISTRY_FILES_WEB);
					if(file_exists(ROOT.REGISTRY_FILES_WEB.$check_rs->get_field('value')))
						@unlink(ROOT.REGISTRY_FILES_WEB.$check_rs->get_field('value'));
					$update_value = save_file_to_folder($name, ROOT.REGISTRY_FILES_WEB);
				}
				
				if(!$this->DataBase->update_sql('registry_value', array('value' => $update_value), array('id' => $value_id, 'path' => $value_path)))
				{
					$this->last_error = $this->Application->Localizer->get_string('database_error');
					return false;
				}
			}
			
		return true;
	}
	
}
?>