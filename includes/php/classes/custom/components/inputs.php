<?
class CInputs
{
	var $Application;
	var $DataBase;
	var $tv;
	var $last_error;

	function CInputs(&$app)
	{
		$this->Application = &$app;
		$this->tv = &$app->tv;
		$this->DataBase = &$this->Application->DataBase;
		$this->Localizer = $this->Application->Localizer;
	}

	function get_last_error()
	{
		return $this->last_error;
	}

	function generate_uri($table, $title, $parent_id = null){
		$uri = $this->generate_uri_ereg(translit($title));

		if(is_null($parent_id))
		$cnt_rs = $this->DataBase->select_custom_sql("SELECT count(id) as cnt FROM {$table} WHERE uri='{$uri}'");
		else
		$cnt_rs = $this->DataBase->select_custom_sql("SELECT count(id) as cnt FROM {$table} WHERE uri='{$uri}' AND parent_id = {$parent_id}");
		if($cnt_rs === false){
			return  array('errors' => $this->Localizer->get_string('internal_error'));
		}

		if($cnt_rs->get_field('cnt') > 0){
			return  array('errors' => $this->Localizer->get_string('object_or_uri_exists'));
		}

		return array('uri' => $uri, 'errors' => false);
	}

	function generate_uri_ereg($string) {
		//return ereg_replace('\-{2,}', '-', ereg_replace('[^a-z0-9]', '-', strtolower($string)));
		$str = ereg_replace('[^a-z0-9]{1,}', '-', strtolower($string));
		if ($str[strlen($str) - 1] == "-") {
			$str = substr($str, 0, strlen($str) - 1);
		}
		return $str;
	}

	function generate_ls_name($string)
	{
		if(!is_string($string))
		return  array('errors' => $this->Localizer->get_string('invalid_input'));

		return array('name' => strtolower(preg_replace('/[^a-z0-9]+/iu', '_', trim($string))));
	}

	function generate_parent_title($parent_id)
	{
		if(!is_numeric($parent_id))
		return array('errors' => $this->Localizer->get_string('invalid_input'));

		$lang_rs = $this->Localizer->get_languages();
		if($lang_rs === false || $lang_rs->eof())
		return array('errors' => $this->Localizer->get_string('internal_error'));

		$sql = "SELECT ";
		while (!$lang_rs->eof())
		{
			$sqls[] = "CONCAT(
					IF(c.parent_id > 0, 
						CONCAT((SELECT value FROM category_parent_title WHERE category_id = c.id AND language_id = {$lang_rs->get_field('id')}), '::')
					, ''),
					(SELECT value FROM category_title WHERE category_id = c.id AND language_id = {$lang_rs->get_field('id')})
			) as {$lang_rs->get_field('title')}";
			$lang_rs->next();
		}
		$sql .= join(', ', $sqls)." FROM category c WHERE c.id = {$parent_id}";
		$check_rs = $this->DataBase->select_custom_sql($sql);
		if($check_rs !== false && !$check_rs->eof())
		{
			$check_rs->first();
			$arr = array();
			row_to_vars($check_rs, $arr);
			return array('arr' => $arr);
		}

		return false;
	}

	function generate_category_title($category_id, $lang_id = null)
	{
		if(!is_numeric($category_id))
		return array('errors' => $this->Localizer->get_string('invalid_input'));

		if(is_null($lang_id) || !is_numeric($lang_id))
		$lang_id = $this->tv['language_id'];

		$sql = "
    	SELECT
    		IF(c.parent_id > 0, CONCAT(cpt.value, '::', ct.value), ct.value) as title
    	FROM
    		category c join category_title ct on ((ct.category_id = c.id AND ct.language_id = {$lang_id})) left join category_parent_title cpt on ((cpt.category_id = c.id AND cpt.language_id = {$lang_id}))
    	WHERE
    		c.id = {$category_id}
    	";	
		$rs = $this->DataBase->select_custom_sql($sql);

		return (($rs !== false && !$rs->eof()) ? $rs->get_field('title') : array('errors' => $this->Localizer->get_string('internal_error')));
	}

	function add_software_from_pad()
	{
		$pad_rs = $this->DataBase->select_sql('padfile', array('status' => PAD_STATUS_NOT_UPLOADED), 0, null, true, '1');
		if($pad_rs === false)
		return array('errors' => $this->Localizer->get_string('database_error'));

		if($pad_rs->eof())
		return array('success' => $this->Localizer->get_string('parsing_finished'));

		if(strlen($pad_rs->get_field('url')) > 0)
		{
			require_once(BASE_CLASSES_PATH.'components/xml.php');
			$xml = new XMLToArray( $pad_rs->get_field('url'), array(), array(), true, false );
			//$xml = new XMLToArray( 'http://12ghosts.com/pad/pad_lookup.xml', array(), array(), true, false );
			$software = $this->Application->get_module('Software');
			//print_arr($pad_rs->get_field('url'));
			//return false;
			$field_rs = $software->get_fields();
			$lang_rs = $this->Application->Localizer->get_languages();
			$xml_arr = $xml->getArray();
			//print_arr($xml_arr);


			if(!is_array($xml_arr))
			{
				$this->DataBase->update_sql('padfile', array('status' => PAD_STATUS_ERROR), array('id' => $pad_rs->get_field('id')));
				return array('errors' => $this->Localizer->get_string('invalid_xml').': <a href="'.$pad_rs->get_field('url').'">'.$pad_rs->get_field('url').'</a>');
			}
			if($field_rs == false)
			return array('errors' => $this->Localizer->get_string('field_recordset_is_empty'));

			if($lang_rs == false)
			return array('errors' => $this->Localizer->get_string('language_recordset_is_empty'));

			$arr = array();
			while (!$field_rs->eof())
			{
				$paths = explode('/', $field_rs->get_field('xml_path'));
				$tmp = $xml_arr;
				for($i = 0; $i < count($paths); $i++)
				{
					if($paths[$i] === '{loc_xml_path}')
					{
						$lang_rs->first();
						while (!$lang_rs->eof())
						{
							$arr[$paths[$i + 1].':#:'.$lang_rs->get_field('title')] = $tmp[$lang_rs->get_field('xml_alias')][$paths[$i + 1]];
							$lang_rs->next();
						}
						$i = count($paths);
						continue;
					}
					elseif(!isset($tmp[$paths[$i]]))
					{
						$arr[$paths[$i]] = '';
						continue;
					}
					elseif(is_array($tmp[$paths[$i]]))
					{
						$arr[$paths[$i]] = '';
						$tmp = $tmp[$paths[$i]];
						continue;
					}
					else
					{
						$arr[$paths[$i]] = $tmp[$paths[$i]];
					}
				}
				$field_rs->next();
			}

			if(strlen($arr[''.CUSTOM_FIELD_CATEGORY_ID.'']) > 0)
			$category_rs = $software->get_category_by_title($arr[''.CUSTOM_FIELD_CATEGORY_ID.'']);

			$pad_status = PAD_STATUS_UPLOADED;
			if(!$category_rs || $category_rs == false)
			{
				$arr['category_id'] = 0;
				$arr['status'] = SOFT_STATUS_ERROR;
				$pad_status = PAD_STATUS_ERROR;
			}
			else
			{
				$arr['category_id'] = $category_rs->get_field('id');
				$arr['status'] = SOFT_STATUS_PENDING_APPROVE;
			}

			//print_arr($arr);
			//return false;
			if(!$software->add_software($arr))
			{
				$this->DataBase->update_sql('padfile', array('status' => PAD_STATUS_ERROR), array('id' => $pad_rs->get_field('id')));
				return array('errors' => $software->get_last_error());
			}
			else
			{
				$this->DataBase->update_sql('padfile', array('status' => $pad_status), array('id' => $pad_rs->get_field('id')));
				return array('info' => true);
			}
		}
		else
		return array('errors' => $this->Localizer->get_string('database_error'));

	}

	function rebuild_positions($table, $id_field = 'id', $pos_field = 'position', $field_cond = false)
	{
		if(!$field_cond || $field_cond == 'false'){
			$rs = $this->DataBase->select_custom_sql("
				SELECT {$id_field}, {$pos_field}
				FROM %prefix%{$table} 
			ORDER by {$pos_field} ASC");
			if($rs !== false && !$rs->eof())
			{
				$position = 1;
				while (!$rs->eof())
				{
					if(!$this->DataBase->update_sql($table, array($pos_field => $position), array('id' => $rs->get_field($id_field))))
					{
						$this->last_error = $this->Application->Localizer->get_string('database_error');
						return false;
					}
					$position++;
					$rs->next();
				}
				return true;
			}
		}
		else{
			$field_cond = str_replace('_dbposition', '', $field_cond);
			$cond_val_rs = $this->DataBase->select_custom_sql("SELECT {$field_cond} FROM %prefix%{$table} GROUP by {$field_cond}");
			$rs = $this->DataBase->select_custom_sql("
				SELECT {$id_field}, {$pos_field}, {$field_cond}
				FROM %prefix%{$table} 
			ORDER by {$pos_field} ASC");
			if($rs !== false && !$rs->eof() && $cond_val_rs !== false && !$cond_val_rs->eof())
			{
				while (!$cond_val_rs->eof())
				{
					$position = 1;
					$rs->first();
					while (!$rs->eof())
					{
						if($rs->get_field($field_cond) == $cond_val_rs->get_field($field_cond))
						{
							if(!$this->DataBase->update_sql($table, array($pos_field => $position), array('id' => $rs->get_field($id_field))))
							{
								$this->last_error = $this->Application->Localizer->get_string('database_error');
								return false;
							}
							$position++;
						}
						$rs->next();
					}
					$cond_val_rs->next();
				}
				return true;
			}
		}
		return false;
	}

	function swap_positions($table, $id_r, $id_s, $id_field = 'id', $pos_field = 'position', $field_cond = false, $cond_val = false)
	{
		if(!$table || !$id_r || !$id_s)
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		$field_cond = str_replace('_dbposition', '', $field_cond);
		
		$rs = $this->DataBase->select_custom_sql("SELECT {$id_field}, {$pos_field} FROM %prefix%{$table} WHERE ({$id_field} = '{$id_r}' OR {$id_field} = '{$id_s}')".(($field_cond && $cond_val && $field_cond !== 'false') ? " AND {$field_cond} = '{$cond_val}'" : ''));
		if($rs !== false && !$rs->eof() && $rs->get_record_count() == 2)
		{
			$pos1 = $rs->get_field($pos_field);
			$rs->next();
			$pos2 = $rs->get_field($pos_field);
			$rs->first();

			if(!$this->DataBase->update_sql($table, array($pos_field => $pos2), array($id_field => $rs->get_field($id_field))))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
			$rs->next();
			if(!$this->DataBase->update_sql($table, array($pos_field => $pos1), array($id_field => $rs->get_field($id_field))))
			{
				$this->last_error = $this->Application->Localizer->get_string('database_error');
				return false;
			}
			return true;
		}

		return false;
	}

	function move_into_pos($table, $id, $pos, $id_field = 'id', $pos_field = 'position', $field_cond = false, $cond_val = false)
	{
		if(!$table || !$id || !$pos)
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		$field_cond = str_replace('_dbposition', '', $field_cond);
		
		$old_rs = $this->DataBase->select_sql($table, array($id_field => $id));
		if($old_rs !== false && !$old_rs->eof())
		{
			$old_pos = $old_rs->get_field($pos_field);
			$start_pos = (($old_pos > $pos) ? $pos : $old_pos);
			$end_pos = (($old_pos < $pos) ? $pos : $old_pos);
			
			$upd_rs = $this->DataBase->select_custom_sql("
				SELECT {$id_field}, {$pos_field} FROM {$table} WHERE {$pos_field} >= {$start_pos} AND {$pos_field} <= {$end_pos} AND {$id_field} <> {$id}
			".(($field_cond && $cond_val && $field_cond !== 'false') ? " AND {$field_cond} = '{$cond_val}'" : ''));
			if($upd_rs !== false && !$upd_rs->eof())
			{
				$ids = array();
				while (!$upd_rs->eof())
				{
					$ids[] = $upd_rs->get_field($id_field);
					$upd_rs->next();
				}
				if(!empty($ids))
				{
					$this->DataBase->select_custom_sql("UPDATE {$table} SET {$pos_field} = {$pos_field} ".(($pos > $old_pos) ? '-' : '+')." 1 WHERE id in(".join(',', $ids).")");
					$this->DataBase->update_sql($table, array($pos_field => $pos), array($id_field => $id));
					return true;
				}
			}
			
		}
		
		return false;
	}
};
?>