<?php
require_once('control.php');
if(!defined('DBNAV_PAGE_SIZE')) define('DBNAV_PAGE_SIZE', 10);
class DBNavigator extends CControl
{
	public $name;
	public $object_id;
	public $title;
	public $query;
	public $sort_vars;
	public $default_sort_key;
	public $default_sort_mode;
	public $headers;
	public $total_items = 0;
	public $item_on_page;
	public $row_clickaction;
	public $size;
	public $cache;
	public $cnt_query = false;

	function DBNavigator($object_id, $query, $sort_vars = array(), $default_sort_key = null, $default_sort_mode = 'ASC')
	{
		parent::CControl('DBNavigator', $object_id);
		$this->template = BASE_CONTROLS_TEMPLATE_PATH . 'db_navigator.tpl';
		$this->object_id = $object_id;
		$this->title = &$this->tv['title'];
		$this->row_clickaction = &$this->tv['row_clickaction'];
		$this->total_items = &$this->tv['total_items'];
		$this->headers = array();
		if(is_array($this->query))
		{
			$this->query = $this->query[0];
			$this->cnt_query = $this->query[1];
		}
		else
			$this->query = $query;
		$this->sort_vars = $sort_vars;
		$this->default_sort_key = $default_sort_key;
		$this->default_sort_mode = $default_sort_mode; 
		$this->_table = &$this->tv['_table'];
		$this->bind_data();
	}

	function bind_data()
	{
		$this->check_cache();
		$sort_mode = InGetPost('dbnav_'.$this->object_id.'_sortmode', false);
		if(!$sort_mode)
		{
			if(isset($this->cache['sortmode'])) $sort_mode = $this->cache['sortmode'];
			else $sort_mode = 'ASC';
		}
		else $this->cache['sortmode'] = $sort_mode;
		
		$sort_field = InGetPost('dbnav_'.$this->object_id.'_sortfield', false);
		if(!$sort_field)
		{
			if(isset($this->cache['sortfield'])) $sort_field = $this->cache['sortfield'];
			else $sort_mode = false;
		}
		else $this->cache['sortfield'] = $sort_field;
		
		$order = '';
		if($sort_field)
		{
			$order = ' ORDER by '.$sort_field.' '.$sort_mode;
		}
		elseif(!is_null($this->default_sort_key)) 
			$order = ' ORDER by '.$this->default_sort_key.' '.$this->default_sort_mode;
			
		$numb_page = InGetPost('dbnav_'.$this->object_id.'_page', false);
		if(!$numb_page)
		{
			if(isset($this->cache['page'])) $numb_page = $this->cache['page'];
			else $numb_page = 1;
		}
		else $this->cache['page'] = $numb_page;
		
		$row_numb_before = ($numb_page - 1) * DBNAV_PAGE_SIZE;
		$limit = ' LIMIT '. $row_numb_before .', '. DBNAV_PAGE_SIZE;
		$row_numb_before++;
		$this->tv['row_numb_before'] = $row_numb_before;
		
		$rs = $this->Application->DataBase->select_custom_sql($this->query . $order . $limit);
		$cnt_rs = $this->Application->DataBase->select_custom_sql(((!$this->cnt_query) ? $this->query : $this->cnt_query));
		if($rs == false || $rs->eof() || $cnt_rs == false || $cnt_rs->eof())
		{
			$_SESSION['cache']['AdminBreadcrumb'] = array();
			$_SESSION['cache']['DBNavigator'][$this->tv['_table']] = array();
			$_GET['dbnav_'.$this->object_id.'_page'] = $numb_page = 1;
			$rs = $this->Application->DataBase->select_custom_sql($this->query . $order);
			if($rs == false || $rs->eof())
			{
				$this->tv['navigator_is_empty'] = true;
				return true;
			}
		}
		
		$this->total_items = ((!$this->cnt_query) ? $cnt_rs->get_record_count() : $cnt_rs->get_field('cnt'));
		$cnt_page = ceil($this->total_items / DBNAV_PAGE_SIZE);
		if($numb_page == $cnt_page) $this->tv['row_numb_end'] = $this->total_items;
		else $this->tv['row_numb_end'] = DBNAV_PAGE_SIZE * $numb_page;
		
		$this->tv['size'] = $this->size = $rs->get_record_count();
		
		require_once(BASE_CONTROLS_PATH .'paginator.php');
		$pager_top = new Paginator("{$this->object_id}_top");
		$pager_top->getpost_var = 'dbnav_'.$this->object_id.'_page';
		$pager_top->total_items = $this->total_items;
		$pager_top->curr_page = $numb_page;
		$pager_top->draw_paginator();
		
		$pager_bottom = new Paginator("{$this->object_id}_bottom");
		$pager_bottom->getpost_var = 'dbnav_'.$this->object_id.'_page';
		$pager_bottom->total_items = $this->total_items;
		$pager_bottom->curr_page = $numb_page;
		$pager_bottom->draw_paginator();
		
		$this->title = $this->tv['object_id'] = $object_id;
		$this->tv['curr_page'] = $this->Application->get_module('Navi')->getUri();
		$this->row_clickaction = $this->Application->get_module('Navi')->getUri().".{$this->tv['_table']}_edit&id=";
		$this->tv['object_id'] = $this->object_id;
		$this->tv['sort_fields'] = $this->sort_vars;
		$this->tv['is_checkable'] = $is_checkable = ((in_array('id', $rs->Fields)));
		while (!$rs->eof())
		{
			foreach ($rs->Fields as $field)
			{
				if(!$is_checkable) $this->tv['rows'][$rs->get_current_row()]['num'] = $rs->get_current_row() + 1;
				$this->tv['rows'][$rs->get_current_row()][$field] = $rs->get_field($field);
			}
			$rs->next();
		}
		
		$this->tv['dbposition_show'] = false;
		if($is_checkable) $this->add_header('id');
		else $this->add_header('num');
		
	}
	
	function check_cache()
	{
		if(!isset($_SESSION['cache'])) $_SESSION['cache'] = array();
		if(!isset($_SESSION['cache']['DBNavigator'][$this->object_id])) $_SESSION['cache']['DBNavigator'][$this->object_id] = array();
		$this->cache = &$_SESSION['cache']['DBNavigator'][$this->object_id];
	}

	function add_header($sort_name, $sort_var = null){
		$res = array_push($this->headers, new DBNavigatorHeader($this->object_id, $sort_name, $sort_var));
		return ($res - 1);
	}

	function in_headers($name){
		if (array_key_exists($name, $this->headers));
	}

	function &get_header($name){
		return $this->headers[$name];
	}
	
	/**
	 * @package ArtCMS.Base
	 */
	/*
	--------------------------------------------------------------------------------
	function swapPosition v1.0
	function use for simple move positions in DBNavigator
	
	For internal use only
		- query: sql string without WHERE, ORDER and GROUP functions
		- filter_arr: array( condition_field => text (for label) )
		- filter_val: array( condition_field => array or recordset for select )
		- position_fields: array( condition_field => position_field ) or position field
		
	history:
		v 1.0 - created (AT)
	*/
	
	function swapPosition($query, $filter_arr = array(), $filter_val = array(), $position_fields = 'position', $mode = 'ASC', $id_field = 'id', $text_field = 'title')
	{
		if($mode !== 'ASC' && $mode !== 'DESC')
			$mode = 'ASC';
			
		if(!empty($filter_val))
			foreach ($filter_val as $name => $values)
				CInput::set_select_data($name, $values);
		
		$position_field = ((is_array($position_fields) && !empty($position_fields)) ? current($position_fields) : $position_fields);
		$position_rs = $this->Application->DataBase->select_custom_sql($query. " ORDER by {$position_field} {$mode}");
		if($position_rs !== false && !$position_rs->eof())
		{
			$filters = $this->tv['dbposition_filters'] = ((is_array($filter_arr) && !empty($filter_arr)) ? $filter_arr : false);
			$this->tv['dbposition_pos_fields'] = $position_fields;
			$this->tv['dbposition_show'] = true;
			$tv_p = &$this->tv['dbposition'];
			$this->tv['dbposition_id_field'] = $id_field;
			while (!$position_rs->eof())
			{
				$tv_p[$position_rs->current_row]['id'] = $position_rs->get_field($id_field);
				$tv_p[$position_rs->current_row]['text'] = $position_rs->get_field($text_field);
				if(is_array($position_fields))
					foreach ($position_fields as $cond_field => $pos_field)
						$tv_p[$position_rs->current_row][$pos_field] = $position_rs->get_field($pos_field);
				else 
					$tv_p[$position_rs->current_row][$position_field] = $position_rs->get_field($position_field);
				if($filters)
					foreach ($filters as $field => $text)
						$tv_p[$position_rs->current_row][$field] = $position_rs->get_field($field);
						
				$position_rs->next();
			}
		}
		else 
			$this->tv['dbposition_show'] = false;
	}

}

class DBNavigatorHeader{

	public $tv;
	public $cache;
	
	function DBNavigatorHeader($object_id, $field_name, $sort_var = null){
		if(!isset($GLOBALS['control_tv']['DBNavigator'][$object_id]['headers'])) $GLOBALS['control_tv']['DBNavigator'][$object_id]['headers'] = array();
		$GLOBALS['control_tv']['DBNavigator'][$object_id]['headers'][$field_name] = array();
		$this->cache = &$_SESSION['cache']['DBNavigator'][$object_id];
		$this->tv = &$GLOBALS['control_tv']['DBNavigator'][$object_id]['headers'][$field_name];
		$this->tv['wrap'] = false;
		$this->tv['mail'] = false;
		$this->tv['clickable'] = true;
		$this->tv['clckaction'] = $GLOBALS['control_tv']['DBNavigator'][$object_id]['row_clickaction'];
		$this->tv['hidden'] = true;
		$this->tv['title'] = $field_name;
		$this->tv['align'] = 'left';
		$this->tv['valign'] = 'middle';
		$this->tv['sortvar'] = $sort_var = ((!is_null($sort_var)) ? $sort_var : $field_name);
		$this->tv['is_sort'] = (bool)((in_array($sort_var, $GLOBALS['control_tv']['DBNavigator'][$object_id]['sort_fields'])));
		$sort_field = InGetPost('dbnav_'.$object_id.'_sortfield', false);
		if(!$sort_field && isset($this->cache['sortfield'])) $sort_field = $this->cache['sortfield'];
		$sort_mode = InGetPost('dbnav_'.$object_id.'_sortmode', false);
		if(!$sort_mode && isset($this->cache['sortmode'])) $sort_mode = $this->cache['sortmode'];
		
		if($sort_field == $field_name)
		{
			$this->tv['sort_act'] = $sort_mode;
			$this->tv['sort_link'] = $GLOBALS['app']->get_module('Navi')->getUri();
			foreach ($_GET as $varname => $value)
				if($varname !== 'r' && $varname !== "dbnav_{$object_id}_sortmode" && $varname !== "dbnav_{$object_id}_sortfield")
					$this->tv['sort_link'] .= "&{$varname}={$value}";
			$this->tv['sort_link'] .= "&dbnav_{$object_id}_sortmode=".(($sort_mode == 'ASC') ? 'DESC' : 'ASC')."&dbnav_{$object_id}_sortfield=".$field_name;
		}
		else 
		{
			$this->tv['sort_act'] = false;
			$this->tv['sort_link'] = $GLOBALS['app']->get_module('Navi')->getUri();
			foreach ($_GET as $varname => $value)
				if($varname !== 'r' && $varname !== "dbnav_{$object_id}_sortmode" && $varname !== "dbnav_{$object_id}_sortfield")
					$this->tv['sort_link'] .= "&{$varname}={$value}";
			$this->tv['sort_link'] .= "&dbnav_{$object_id}_sortmode=ASC&dbnav_{$object_id}_sortfield=".$field_name;
		}
	}

	/**
        * set to true to normalize price values
    */

	function set_width($width){
		$this->tv['width'] = strval($width);
	}

	function set_wrap($wrap = false){
		$this->tv['wrap'] = $wrap;
	}

	function set_mail($mail = true){
		$this->tv['mail'] = (bool)$mail;
		$this->tv['clickable'] = !$mail;
	}

	function set_click($clickable = true, $clckaction = null){
		$this->tv['clickable'] = (bool)$clickable;
		if(!is_null($clckaction))
			$this->tv['clckaction'] = $clckaction;
	}

	function set_valign($val){
		if (!in_array($val, array('top', 'middle', 'bottom'))) system_die('Invalid valign mode', 'Header_link->set_valign');
		$this->tv['valign'] = $val;
	}
	function set_align($val){
		if (!in_array($val, array('left', 'center', 'right'))) system_die('Invalid align mode', 'Header_link->set_align');
		$this->tv['align'] = $val;
	}
	
	function set_title($str){
		if(!strval($str)) system_die('Invalid title', 'Header_link->set_title');
		$this->tv['title'] = $str;
	}
	
	function set_position()
	{
		$this->tv['swapPosition'] = true;
		$this->tv['clickable'] = false;
	}
}
?>