<?php
/**
 * @package Art-cms
 */
/*
history:
	v 1.0.0 - created (AT)
--------------------------------------------------------------------------------
*/
/**
 */
require_once('recordset.php');

/**
 * @package Art-cms
 * @version 1.0
 */
class CBaseDB
{
	/**
	 * @var CApplication
	 * @access protected
	 */
	var $Application;
	/**
	 * @var integer/boolean
	 * @access protected
	 */
	var $LinkID = false;
	/**
	 * @var string
	 * @access protected
	 */
	var $last_error;
	/**
	 * @var boolean
	 * @access protected
	 */
	var $can_connect = true;
	/**
	 * @var integer
	 * @access protected
	 */
	var $_int_data_seek = 0;

	var $now_stmt = '';
	var $datetime_stmt = '';
	var $clob_stmt = '';
	var $auto_inc_stmt = '';
	var $concat_func_stmt = '';
	var $concat_char = '';

	/**
	 * Constructor
	 * @param CApplication &$app
	 */
	function CBaseDB(&$app)
	{
		$this->Application = &$app;
	}
	/**
	 * @param integer/array $limit
	 * @return string
	 * @access protected
	 */
	function _set_limit($limit=null){system_die('pure virtual call _set_limit()');}
	/**
	 * lock tables in database
	 * @param array $tables table names
	 * @access public
	 */
	function lock($tables=null){system_die('pure virtual call lock()');}
	/**
	 * unlock tables in database
	 * @access public
	 */
	function unlock(){system_die('pure virtual call unlock()');}
	/**
	 * returns last auto-generated ID value
	 * @return integer/boolean
	 * @access public
	 */
	function get_last_id(){system_die('pure virtual call get_last_id()');}
	/**
	 * returns version of the RDBMS
	 * @return float
	 * @access public
	 */
	function version(){system_die('pure virtual call free()');}
	/**
	 * @access protected
	 */
	function free($id){system_die('pure virtual call free()');}
	/**
	 * @return boolean
	 * @access protected
	 */
	function internalConnect(){system_die('pure virtual call internalConnect()');}
	/**
	 * @access protected
	 */
	function internalDisconnect(){system_die('pure virtual call internalDisconnect()');}
	/**
	 * @param string $str
	 * @return resource/boolean
	 * @access protected
	 */
	function internalQuery($str){system_die('pure virtual call internalQuery()');}
	/**
	 * escapes value to use in DML statements
	 * @param string $s
	 * @return string
	 * @access public
	 */
	function internalEscape($s){system_die('pure virtual call internalEscape()');}
	/**
	 * escapes value to use in Like statements
	 * @param string $s
	 * @return string
	 * @access public
	 */
	function internalLikeEscape($s){system_die('pure virtual call internalLikeEscape()');}
	/**
	 * @param resource $r
	 * @return integer
	 * @access protected
	 */
	function internalNumFields($r){system_die('pure virtual call internalNumFields()');}
	/**
	 * @param resource $r
	 * @param integer $i
	 * @return object
	 * @access protected
	 */
	function internalFetchField($r, $i){system_die('pure virtual call internalFetchField()');}
	/**
	 * returns 0-based array
	 */
	function internalFetchRow($r){system_die('pure virtual call internalFetchRow()');}
	/**
	 * returns 0-based and assoc array
	 * @param resource $r
	 * @return array
	 * @access protected
	 */
	function internalFetchArray($r){system_die('pure virtual call internalFetchArray()');}
	/**
	 * returns assoc array
	 * @param resource $r
	 * @return array
	 * @access protected
	 */
	function internalFetchAssoc($r){system_die('pure virtual call internalFetchAssoc()');}
	/**
	 * returns rows count
	 */
	function internalNumRows($r){system_die('pure virtual call internalNumRows()');}
	/**
	 * check whether the table exists in db
	 */
	function is_table($TableName){system_die('pure virtual call is_table()');}
	/**
	 * returns size of the table
	 * @param string $table_name
	 * @return integer
	 * @access public
	 */
	function get_table_count($table_name, $pk_arr = 0)
	{
		$tbl_cnt = $this->select_sql($table_name, $pk_arr, 0, array('count(*) as cnt'));
		if ( ($tbl_cnt!==false) && (!$tbl_cnt->eof()) )
			return $tbl_cnt->get_field('cnt');
		else
			return -1;
	}

	/**
	 * @param string $table_name
	 * @param integer/array $limit
	 * @return string
	 * @access protected
	 */
	function get_sql($table_name, $pk_arr = 0, $or_arr = 0, $fields = null, $add_prefix = true, $limit = null)
	{
		if (strcasecmp(substr($table_name, 0, 6), 'select') == 0)
			$sql = str_replace('%prefix%', ((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX), $table_name);
		else
			$sql = 'select '.(($fields==null)?('*'):(implode(',',$fields))).' from ' . ( ($add_prefix)?(((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX)):('') ) . $table_name;
		$s_fld = '';
		if (is_array($pk_arr))
			foreach ($pk_arr as $k => $v)
				$s_fld .= ( ($s_fld=='')?(''):(' and ') ) . $k . '=\''.$this->internalEscape($v).'\'';
		if ($s_fld) $sql .= ' where ' . $s_fld;
		$o_fld = '';
		if (is_array($or_arr))
			foreach ($or_arr as $k => $v)
				$o_fld .= ( ($o_fld=='')?(''):(', ') ) . $k . ' ' . $v;
		if ($o_fld != '')
			$sql .= ' order by ' . $o_fld;

		// limit = limit OR array(page,items)
		$sql = $this->_set_limit($sql, $limit);

		return $sql;
	}

	/**
	 * @param string $table_name
	 * @param integer/array $limit
	 * @return CRecordSet/boolean
	 * @access public
	 */
	function &select_sql($table_name, $pk_arr = 0, $or_arr = 0, $fields = null, $add_prefix = true, $limit = null)
	{
		$s = &$this->select_custom_sql($this->get_sql($table_name, $pk_arr, $or_arr, $fields, $add_prefix), $limit);
		return $s;
	}

	/**
	 * @param string $sql
	 * @param integer/array $limit
	 * @return CRecordSet/boolean
	 * @access public
	 */
	function &select_custom_sql($sql, $limit = null)
	{
		// limit = limit OR array(page,items)
		$sql = $this->_set_limit($sql, $limit);
		$tbl_row = $this->internalQuery($sql);
		if ($tbl_row) {
			$rcd = new CRecordSet();
			$f_n = $this->internalNumFields($tbl_row);
			for ($i=0; $i<$f_n; $i++) {
				$f_o = $this->internalFetchField($tbl_row, $i);
				$rcd->add_field($f_o->name);
			}
			$cnt = 0;
			while ($cnt++ < $this->_int_data_seek) $this->internalFetchAssoc($tbl_row);
			while ( ($a_arr = $this->internalFetchAssoc($tbl_row)) ) $rcd->add_row($a_arr);
			$this->free($tbl_row);
			return $rcd;
		}
		else
		{
			$return = false;
			return $return;
		}
	}
	
	/**
	 * @param string $table_name
	 * @access public
	 * @deprecated
	 */
	function update_from_tv($table_name, $tv, $Keys, $up_fld = 0)
	{
		$vl = array();
		$rcd = $this->select_sql($table_name, 0, 0, null, true, 1);
		if ($rcd!==false)
			foreach ($rcd->Fields as $k => $v)
				if (isset($tv[$v]))
					if ( (!is_array($up_fld)) || ( (is_array($up_fld)) && (in_array($v, $up_fld)) ) )
						$vl[$v] = $tv[$v];
		return $this->update_sql($table_name, $vl, $Keys);
	}

	/**
	 * example to use query update table set f1=v1 and f2=v2 where f3=v3 and f4=v4<br>
	 * run this procedure<br>
	 * update('table', array('f1'=>'v1', 'f2'=>'v2'), array('f3'=>'v3', 'f4'=>'v4'));<br>
	 * @param string $table_name
	 * @access public
	 */
	function update_sql($table_name, $UpdateFields, $Keys)
	{
		$sql = 'update ' . ((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX) . $table_name . ' set ';
		$c = '';
		foreach ($UpdateFields as $k => $v)
		{
			if ( is_null( $v ) )
				$sql .= $c . $k . '=null';
			elseif (strcmp($v, 'now()') != 0)
				$sql .= $c . $k . '=\''.$this->internalEscape($v).'\'';
			else
				$sql .= $c . $k . '=' . $this->now_stmt;
			$c = ', ';
		}

		$sql .= ' where ';
		$c = '';
		foreach ($Keys as $k => $v) {
			$sql .= $c . $k . '=\''.$this->internalEscape($v).'\'';
			$c = ' and ';
		}
		return $this->internalQuery($sql);
	}

	/**
	 * @param string $table_name
	 * @return integer/boolean
	 * @access public
	 * @deprecated
	 */
	function insert_from_tv($table_name, $tv)
	{
		$vl = array();
		$rcd = $this->select_sql($table_name, 0, 0, null, true, 1);
		if ($rcd!==false)
			foreach ($rcd->Fields as $k => $v)
				if (isset($tv[$v]))
					$vl[$v] = $tv[$v];
		return $this->insert_sql($table_name, $vl);
	}

	/**
	 * @param string $table_name
	 * @return integer/boolean
	 * @access public
	 */
	function insert_sql($table_name, $values, $have_id = true)
	{
		$sql = 'insert into ' . ((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX) . $table_name . ' (';
		$c = '';
		foreach ($values as $k => $v) {
			$sql .= $c . $k;
			$c = ', ';
		}
		$sql .= ') values(';
		$c = '';
		foreach ($values as $k => $v)
		{
			if (is_null($v))
				$sql .= $c . 'null';
			elseif (strcasecmp($v, 'now()') != 0)
			{
				if (is_bool($v)) $v = (($v)?(1):(0));
				$sql .= $c . '\''.$this->internalEscape($v).'\'';
			}
			else
				$sql .= $c . $this->now_stmt;
			$c = ', ';
		}
		$sql .= ')';
		if (!$this->internalQuery($sql)) return false;
		elseif($have_id)
			return $this->get_last_id();
		return true;
	}

	/**
	 * @param string $table_name
	 * @access public
	 */
	function delete_sql($table_name, $pk_arr = 0)
	{
		$s_fld = '';
		if (is_array($pk_arr))
			foreach ($pk_arr as $k => $v)
				$s_fld .= ( ($s_fld=='')?(''):(' and ') ) . $k . '=\''.$this->internalEscape($v).'\'';

		$sql = 'delete from ' . ((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX) . $table_name;
		if ($s_fld != '')
			$sql .= ' where ' . $s_fld;

		return $this->internalQuery($sql);
	}

	/**
	 * @param string $sql
	 * @access public
	 */
	function custom_sql($sql)
	{
		return ($this->internalQuery($sql) !== false);
	}

	/**
	 * @return string
	 * @access public
	 */
	function get_last_error()
	{
		return $this->last_error;
	}
}
?>