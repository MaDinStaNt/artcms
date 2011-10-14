<?php
require_once('control.php');
$_where = array();

class AdminFilters extends CControl{
	public $filter_arr;
	public $object_id;
	public $cache;
	public $formname;

	function AdminFilters($filter_arr, $object_id, $formname = null)
	{
		if(!is_array($filter_arr)) system_die('Invalid input $filter_arr', "AdminFilters[{$object_id}]->filter_arr");
		parent::CControl('AdminFilters', $object_id);
		$this->template = BASE_CONTROLS_TEMPLATE_PATH .'adminfilters.tpl';
		$this->filter_arr = $filter_arr;
		$this->build_filter();
		$this->object_id = $this->tv['object_id'] = $object_id;
		$this->formname = (is_null($formname)) ? $object_id : $formname;
		$this->check_session();
	}

	function build_filter()
	{
		$this->tv['filter'] = $this->filter_arr;
		foreach ($this->filter_arr as $field => $data)
		{
			if($data['type'] == FILTER_SELECT)
			{
				if(!isset($data['data'])) system_die('Not defined \'data\' for type FILTER_SELECT', "AdminFilter->{$this->object_id}");
				CInput::set_select_data('adminfilter_'.$field, $data['data'][0], $data['data'][1], $data['data'][2]);
			}
			elseif($data['type'] == FILTER_ENUM)
			{
				if(!isset($data['data'])) system_die('Not defined \'data\' for type FILTER_SELECT', "AdminFilter->{$this->object_id}");
				CInput::set_radio_data('adminfilter_'.$field, $data['data'][0], $data['data'][1], $data['data'][2]);
			}
			elseif($data['type'] == FILTER_TEXT) CValidator::add_nr($field, VRT_TEXT);
		}
	}

	function check_session()
	{
		global $app;
		if(!isset($_SESSION['Filter'])) $_SESSION['Filter'] = array();
		if(!isset($_SESSION['Filter'][$this->object_id])) $_SESSION['Filter'][$this->object_id] = array();
		$this->cache = &$_SESSION['Filter'][$this->object_id];
		if(!$this->check_submit())
		{
			if(!empty($this->cache))
				foreach ($this->cache as $field => $value)
					$app->tv['adminfilter_'.$field] = $this->tv['adminfilter_'.$field] = $value;
			$this->get_condition_str();
		}
	}

	function check_submit()
	{
		global $app;
		if(CForm::is_submit($this->formname, 'clear'))
			$this->cache = array();
		elseif (CForm::is_submit($this->formname, 'filter'))
		{
			if(CValidator::validate_input())
			{
				$_SESSION['cache']['AdminBreadcrumb'] = array();
				$_SESSION['cache']['DBNavigator'][$this->tv['_table']] = array();
				foreach ($app->tv as $field => $value)
					if(preg_match('/^adminfilter_[a-zA-Z0-9_-]+#[a-zA-Z0-9_-]+$/', $field))
					{
						$this->cache[str_replace('adminfilter_', '', $field)] = $value;
						$this->tv[$field] = $value;
					}
					elseif(preg_match('/^adminfilter_[a-zA-Z0-9_-]+$/', $field))
					{
						$this->cache[str_replace('adminfilter_', '', $field)] = $value;
						$this->tv[$field] = $value;
					}
				$this->get_condition_str();
			}
			else
				$app->tv['_errors'] = CValidator::get_errors();
			
			return true;
		}
		
		return false;
	}

	function get_condition_str()
	{
		global $app, $_where;
		$_where[$this->formname] = '1=1';
		foreach ($this->tv as $field => $value)
			if(preg_match('/^adminfilter_[a-zA-Z0-9_-]+#[a-zA-Z0-9_-]+$/', $field))
			{
				$field = str_replace('adminfilter_', '', $field);
				switch ($this->filter_arr[$field]['condition'])
				{
					case CONDITION_EQUAL:
						if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', $field).'="'.mysql_escape_string($value).'"';
						break;
					case CONDITION_LIKE:
						if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', $field).' LIKE "'.mysql_escape_string($value).'%"';
						break;
				}
				if($this->filter_arr[str_replace('_from', '', $field)]['type'] == FILTER_DATE)
				{
					if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', str_replace('_from', '', $field)).' >= "'.mysql_escape_string($value).'"';
				}
				if($this->filter_arr[str_replace('_to', '', $field)]['type'] == FILTER_DATE)
				{
					if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', str_replace('_to', '', $field)).' <= "'.mysql_escape_string($value).'"';
				}
			}
			elseif(preg_match('/^adminfilter_[a-zA-Z0-9_-]+$/', $field))
			{
				$field = str_replace('adminfilter_', '', $field);
				switch ($this->filter_arr[$field]['condition'])
				{
					case CONDITION_EQUAL:
						if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', $field).'="'.mysql_escape_string($value).'"';
						break;
					case CONDITION_LIKE:
						if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', $field).' LIKE "'.mysql_escape_string($value).'%"';
						break;
				}
				if($this->filter_arr[str_replace('_from', '', $field)]['type'] == FILTER_DATE)
				{
					if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', str_replace('_from', '', $field)).' >= "'.mysql_escape_string($value).'"';
				}
				if($this->filter_arr[str_replace('_to', '', $field)]['type'] == FILTER_DATE)
				{
					if(strlen($value) > 0) $_where[$this->formname] .= ' AND '.str_replace('#', '.', str_replace('_to', '', $field)).' <= "'.mysql_escape_string($value).'"';
				}
			}
	}
}
?>