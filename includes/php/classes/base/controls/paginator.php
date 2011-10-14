<?php
require_once('control.php');

class Paginator extends CControl{
	public $total_items;
	public $item_on_page = 10;
	public $number_left_wing = 3;
	public $number_right_wing = 3;
	public $cnt_pages = 1;
	public $template;
	public $getpost_var;
	public $curr_page;
	
	function Paginator($object_id)
	{
		parent::CControl('Paginator', $object_id);
		$this->tv['object_id'] = $object_id;
		$this->curr_page = &$this->tv['curr_page'];
		$this->template = BASE_CONTROLS_TEMPLATE_PATH . 'paginator.tpl';
	}
	
	function draw_paginator()
	{
		if($this->check_input()){
			$this->tv['cnt_pages'] = $this->cnt_pages = ceil($this->total_items / $this->item_on_page);
			if(!$this->curr_page) $this->curr_page = InGetPost($this->getpost_var, 1);
			$this->tv['link'] = $this->Application->get_module('Navi')->getUri()."&{$this->getpost_var}=";
			if($this->cnt_pages > ($this->number_left_wing + $this->number_right_wing))
			{
				$this->tv['lower_total_numb'] = false;
				$this->tv['cnt_left'] = $this->number_left_wing;
				$this->tv['cnt_right'] = $this->number_right_wing;
				$this->tv['right_start'] = $right_start = $this->cnt_pages - $this->number_right_wing + 1;
				
				for($i = $this->number_left_wing + 1; $i < $right_start; $i++)
					$this->tv['pages'][] = $i;
			}
			else
			{
				for($i = 0; $i < $this->cnt_pages; $i++)
				{
					$this->tv['lower_total_numb'] = true;
					$this->tv['pages'][] = $i + 1;
				}
			}
		}
	}
	
	function check_input()
	{
		if(!is_numeric($this->total_items)) system_die( 'Invalid total_items','Paginator->total_items');
		elseif(!is_numeric($this->item_on_page)) system_die( 'Invalid item_on_page','Paginator->item_on_page');
		elseif(!is_numeric($this->number_left_wing)) system_die( 'Invalid number_left_wing','Paginator->number_left_wing');
		elseif(!is_numeric($this->number_right_wing)) system_die( 'Invalid number_right_wing','Paginator->number_right_wing');
		else return true;
		
		return false;
	}
}
?>