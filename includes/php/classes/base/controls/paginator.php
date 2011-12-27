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
			$this->tv['link'] = $this->Application->get_module('Navi')->getUri();
			foreach ($_GET as $varname => $value)
				if($varname !== 'r' && $varname !== $this->getpost_var)
					$this->tv['link'] .= "&{$varname}={$value}";
					
			$this->tv['link'] .= "&{$this->getpost_var}=";

			for($i = 0; $i < $this->cnt_pages; $i++)
				$this->tv['pages'][] = $i + 1;
				
			if($this->cnt_pages > ($this->number_left_wing + $this->number_right_wing))
			{
				$this->tv['show_buttons'] = true;
				$this->tv['left_buttons_show'] = ($this->curr_page > 1);
				$this->tv['right_buttons_show'] = ($this->curr_page < $this->cnt_pages);
				$this->tv['first_page'] = 1;
				$this->tv['last_page'] = $this->cnt_pages;
				$this->tv['prev_page'] = $this->curr_page - 1;
				$this->tv['next_page'] = $this->curr_page + 1;
				if($this->curr_page <= $this->number_left_wing){
					for($i = 0; $i <= ($this->number_left_wing + $this->number_right_wing); $i++)
						$this->tv['showed_pages'][] = $i + 1;
				}
				elseif($this->curr_page > ($this->cnt_pages - $this->number_right_wing))
					for($i = ($this->cnt_pages - $this->number_left_wing - $this->number_right_wing); $i <= $this->cnt_pages; $i++)
						$this->tv['showed_pages'][] = $i;
				else 
					for($i = ($this->curr_page - $this->number_left_wing); $i <= ($this->curr_page + $this->number_right_wing); $i++)
						$this->tv['showed_pages'][] = $i;
			}
			else 
			{
				$this->tv['showed_pages'] = $this->tv['pages'];
				$this->tv['show_buttons'] = false;
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