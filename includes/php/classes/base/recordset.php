<?php
/**
 * @package Art-cms
 */
/*
--------------------------------------------------------------------------------
Class CRecordSetRow v 1.0
For internal use only

history:
	v 1.0 - created (AT)
--------------------------------------------------------------------------------
*/
/**
 * @package Art-cms
 */
class CRecordSetRow {
	var $Fields;
	function CRecordSetRow($arr) {
		$this->Fields = $arr;
	}
	function get_field($fld_name) {
		if ( ($GLOBALS['DebugLevel']) && (!key_exists($fld_name, $this->Fields)) ) system_die('No such field '.$fld_name);
		return $this->Fields[$fld_name];
	}
	function have_field($fld_name) {
		return key_exists($fld_name, $this->Fields);
	}
}
/*
--------------------------------------------------------------------------------
Class CRecordSet v 1.0
Class for sql query result storing and accessing

methods:
	return type:	name:					(version) description:
status functions:
	bool			eof()					(1.1.0) returns true if end of the recordset is reached, false otherwice
	int				get_record_count()		(1.2.0) returns count of rows in recordset
	array			get_2darray(string id = '')	(1.4.0) return 2D associative array of all rows in recordset, numerated by $id or by numbers (DEPRECATED)
manipulate functions:
	void			first()					(1.1.0) goes to first row in record set
	void			last()					(1.1.0) goes to the last row in record set
	void			next()					(1.1.0) goes to the next row in record set
	void			prev()					(1.1.0) goes to the previous row in record set

	void			add_row(array assoc, int type)	(1.5.1) appends or prepends Rows array, type is INSERT_BEGIN or INSERT_END
	bool			find(string key, string value)		(1.5.0) goes to the position where $key equal to $value

	void			free()					(1.2.1) frees the result
data functions:
	CRecordSetRow&	get_row()				(1.2.0) get reference to the whole row in table or 0 if error
	mixed			get_field(string name)	(1.1.0) returns value of the field
	bool			set_field(string name, string value) 	(1.5.2) set value of the field (DEPRECATED)
properties:
	CRecordSetRow[]	Rows					(1.0.0) array of result rows (protected)

history:
	v 1.0.0 - created (AT)
--------------------------------------------------------------------------------
*/
define('INSERT_BEGIN', 1);
define('INSERT_END', 2);

/**
 * @package Art-cms
 */
class CRecordSet
{
	var $Rows;		// protected
	var $Fields;	// initiated in CDataBase
					// format:	[0] => 'field_1'
					//			[1] => 'field_2'

	var $current_row; // index of the current row, for internal use

	function CRecordSet()
	{
		$this->init();
	}

	function find($key, $value)
	{
		$this->first();
		while (!$this->eof())
		{
			if ($this->get_field($key) == $value)
				return true;
			$this->next();
		}
		return false;
	}

	// DEPRECATED
	function get_2darray($id = '', $group = '')
	{
		$a = array();
        $keys = array_keys($this->Rows);
		foreach ($keys as $kv)
		{
			$b = array();
			$v = &$this->Rows[$kv];
			row_to_vars($v, $b);
			if (empty($id))
				if (empty($group))
                	$a[] = $b;
                else
                	$a[$v->get_field($group)][] = $b;
			else
				if (empty($group))
                	$a[$v->get_field($id)] = $b;
                else
                	$a[$v->get_field($group)][$v->get_field($id)] = $b;
		}
		return $a;
	}

	function get_record_count()
	{
		return (count($this->Rows));
	}

	function eof()
	{
		return ($this->current_row >= count($this->Rows));
	}

	function first()
	{
		$this->current_row = 0;
	}

	function next()
	{
		$this->current_row++;
	}

	function prev()
	{
		if ($this->current_row > 0)
			$this->current_row--;
	}

	function last()
	{
		if (sizeof($this->Rows))
			$this->current_row = count($this->Rows)-1;
	}

	function &get_row() // get whole row
	{
		if (!isset($this->Rows[$this->current_row]))
			system_die('get_row: Reading after eof mark');
		return $this->Rows[$this->current_row];
	}
	
	function get_current_row()
	{
		return $this->current_row;
	}

	function get_field($name) // get value of the field in current row
	{
		if (!isset($this->Rows[$this->current_row]))
			system_die('get_field: Reading after eof mark');
		return $this->Rows[$this->current_row]->get_field($name);
	}

	// DEPRECATED
	function set_field($name, $value) // set value of the field in current row
	{
	  if (!isset($this->Rows[$this->current_row]))
		   system_die('set_field: Reading after eof mark');
	  $this->Rows[$this->current_row]->Fields[$name] = $value;
	  return true;
	}
	 
	function free() // frees the result
	{
		$this->init();
	}

	function add_row($assoc_arr, $to = INSERT_END)
	{
		foreach ($this->Fields as $k => $v) if (!isset($assoc_arr[$v])) $assoc_arr[$v] = '';
		if ($to == INSERT_BEGIN)
			array_unshift($this->Rows, new CRecordSetRow($assoc_arr));
		else
			$this->Rows[] = new CRecordSetRow($assoc_arr);
	}

	function delete_row() // deletes current row
	{
		array_splice($this->Rows, $this->current_row, 1);
	}

	function init() // Protected.
	{
		$this->current_row = 0;
		$this->Rows = array();
		$this->Fields = array();
	}

	function add_field($fld_name) // Protected. For CDataBase use only.
	{
		if (!in_array($fld_name, $this->Fields))
			$this->Fields[] = $fld_name;
	}
}
?>