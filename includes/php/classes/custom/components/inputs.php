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
			return  array('errors' => $this->Application->Localizer->get_string('internal_error'));
		}

		if($cnt_rs->get_field('cnt') > 0){
			return  array('errors' => $this->Application->Localizer->get_string('object_or_uri_exists'));
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
	
	function delete_rp($id)
	{
		if(!is_numeric($id))
		{
			$this->last_error = $this->Application->Localizer->get_string('invalid_input');
			return false;
		}
		
		if(!$this->DataBase->delete_sql('registry_path', array('id' => $id)))
		{
			$this->last_error = $this->Application->Localizer->get_string('database_error');
			return false;
		}
		
		return true;
	}
};
?>