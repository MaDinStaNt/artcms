<?php
/**
 * @package Art-cms
 */
/*
--------------------------------------------------------------------------------
Class CDataBase v 1.0
MS SQL DataBase class
Use this class through the $Application member of CHTMLPage class, ie:
	$this->Application->DataBase->method_name();

definitions:
	array of select/update/insert keys - array('key1'=>'value1', 'key2'=>'value2');
	array of order keys - array('key1'=>'asc', 'key2'=>'desc');

main methods:
	get_table_count('table name without prefix', array of select keys) - return count of rows in table, or -1 if error

	select_sql(
		'table name without prefix',
		array of select keys,
		array of sorting keys,
		array of fields to select,
		true/false to add/don't add global prefix,
		limit - number to get only first rows or array(start, rows)
		) - returns CRecordSet or false if error
	select_custom_sql('sql string') - returns CRecordSet or false if error
	custom_sql('sql string') - makes insert/delete/update and return true/false

	insert_sql('table name without prefix', array of insert keys) - returns auto generated id if any, on error - false
	update_sql('table name without prefix', array of update values, array of condition keys) - returns void
	delete_sql('table name without prefix', array of delete keys) - returns false if error

	free(handle) - frees used handle

history:
	v 1.0.0 - created (AT)
--------------------------------------------------------------------------------
*/

/**
 */
require_once('_db.php');
require_once('recordset.php');

/**
 * @package Art-cms
 * @ignore
 */
class CDataBase extends CBaseDB
{
	function CDataBase(&$app)
	{
		parent::CBaseDB($app);
		$this->now_stmt = 'GETDATE()';
		$this->datetime_stmt = 'DATETIME';
		$this->clob_stmt = 'TEXT';
		$this->auto_inc_stmt = 'identity (1, 1)';
		$this->concat_func_stmt = '';
		$this->concat_char = '||';

		if (!function_exists('mssql_connect'))
			system_die('MSSQL module is not installed');
	}
	// _set_limit
	function _set_limit($sql, $limit)
	{
		$this->_int_data_seek = 0;
		if (!is_null($limit))
			if ( (!is_array($limit)) && (intval($limit) > 0) )
				$sql = preg_replace('/select/i', 'select top '.$limit, $sql, 1); // only first select
			elseif (isset($limit[1]) && isset($limit[0]) && ($limit[1] > 0) && ($limit[0] > 0))
			{
				$sql = preg_replace('/select/i', 'select top '.(intval($limit[0]) * intval($limit[1])), $sql, 1); // only first select
				$this->_int_data_seek = (intval($limit[0])-1) * intval($limit[1]);
			}
		return $sql;
	}
	// free
	function free($id)
	{
		return @mssql_free_result($id);
	}
	// get_last_id
	function get_last_id()
	{
		$result = @mssql_query('SELECT @@IDENTITY', $this->LinkID);
		if ($result === false) return false;
		list($id) = @mssql_fetch_row($result);
		return $id;
	}
	// internalConnect
	function internalConnect()
	{
		if ($this->LinkID === false)
		{
			@mssql_min_error_severity(255);
			@mssql_min_message_severity(255);
			$this->LinkID = @mssql_connect((isset($this->DB_SERVER))?$this->DB_SERVER:DB_SERVER.','.DB_PORT, (isset($this->DB_USER))?$this->DB_USER:DB_USER, (isset($this->DB_PASSWORD))?$this->DB_PASSWORD:DB_PASSWORD);
			if ($this->LinkID !== false)
			{
				if (@mssql_select_db((isset($this->DB_DATABASE))?$this->DB_DATABASE:DB_DATABASE, $this->LinkID))
					return $this->LinkID;
				else
				{
					if (!@mssql_query('create database ['.((isset($this->DB_DATABASE))?$this->DB_DATABASE:DB_DATABASE).']', $this->LinkID))
					{
						$GLOBALS['GlobalDebugInfo']->Write('<font color="red">Cannot create DataBase ['.@mssql_get_last_message().']</font>');
						$this->last_error = 'Cannot create DataBase';
					}
					else
						if (@mssql_select_db((isset($this->DB_DATABASE))?$this->DB_DATABASE:DB_DATABASE, $this->LinkID))
							return $this->LinkID;
						else
						{
							$GLOBALS['GlobalDebugInfo']->Write('<font color="red">Cannot select DataBase ['.@mssql_get_last_message().']</font>');
							$this->last_error = 'Cannot select DataBase';
						}
				}
			}
			else
			{
				$GLOBALS['GlobalDebugInfo']->Write('<font color="red">Cannot connect to DataBase server ['.@mssql_get_last_message().']</font>');
				$this->last_error = 'Cannot connect to DataBase server';
			}
			$this->can_connect = false;
			return false;
		}
		return $this->LinkID;
	}
	// internalDisconnect
	function internalDisconnect()
	{
		if ($this->LinkID > 0) @mssql_close($this->LinkID);
		$this->LinkID = false;
	}
	// internalEscape
	function internalEscape($s)
	{
		return str_replace('\'', '\'\'', $s);
	}
	// internalLikeEscape
	function internalLikeEscape($s)
	{
		$s = str_replace("'", "''", $s);
		$s = str_replace("[", "[[]", $s);
		$s = str_replace("%", "[%]", $s);
		$s = str_replace("_", "[_]", $s);
		return $s;
	}
	function internalFetchRow($r)
	{
		return @mssql_fetch_array($r, MSSQL_NUM);
	}
	// internalFetchArray
	function internalFetchArray($r)
	{
		return @mssql_fetch_array($r, MSSQL_BOTH);
	}
	// internalFetchAssoc
	function internalFetchAssoc($r)
	{
		return @mssql_fetch_array($r, MSSQL_ASSOC);
	}
	// internalNumRows
	function internalNumRows($r)
	{
		return @mssql_num_rows($r);
	}
	// internalFetchField
	function internalFetchField($r, $i)
	{
		return @mssql_fetch_field($r, $i);
	}
	// internalNumFields
	function internalNumFields($r)
	{
		return @mssql_num_fields($r);
	}
	function rows_affected()
	{
		$result = @mssql_query('SELECT @@ROWCOUNT', $this->LinkID);
		if ($result===false) return false;
		list($affected) = @mssql_fetch_row($result);
		return $affected;
	}
	// internalQuery
	function internalQuery($str)
	{
		$this->last_error = '';
		if ( (!$this->can_connect) || (($this->LinkID === false) && (!$this->internalConnect())) ) return false;
		$str = str_replace('%prefix%', ((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX), $str);
		//$str = str_replace('%dmy%', '%m/%d/%Y', $str);
		//$str = str_replace('%dmyhm%', '%m/%d/%Y %H:%i', $str);
		//$str = preg_replace('/auto_increment/i', 'identity (1, 1)', $str);
		//$str = preg_replace('/unique +index/i', 'unique', $str);
		if (!$GLOBALS['DebugLevel'])
			return @mssql_query($str, $this->LinkID);
		else
		{
			$t1 = get_formatted_microtime();
			$id = @mssql_query($str, $this->LinkID);
			$t2 = get_formatted_microtime();
			if ($id!==false)
				$GLOBALS['GlobalDebugInfo']->Write(htmlspecialchars($str) . ' time: '.($t2-$t1).' <font color="green">success - '.$this->rows_affected().' rows</font>');
			else
			{
				$this->last_error = 'Cannot perfom SQL query: '.$str . ' '. @mssql_get_last_message();
				$GLOBALS['GlobalDebugInfo']->Write(htmlspecialchars($str) . ' <font color="red">failed ['.@mssql_get_last_message().']</font>');
				system_die($this->last_error);
			}
			return $id;
		}
	}
	// lock
	function lock($tables=null)
	{
		return $this->custom_sql('BEGIN TRAN');
	}
	// unlock
	function unlock()
	{
		return $this->custom_sql('COMMIT TRAN');
	}
	function is_table($TableName)
	{
		return ($this->get_table_count($TableName) >= 0);
	}
	// version
	function version()
	{
		$this->last_error = '';
		if ( (!$this->can_connect) || ($this->LinkID === false) && (!$this->internalConnect()) ) return false;
		$result = @mssql_query('SELECT @@VERSION', $this->LinkID);
		if ($result===false) return false;
		list($version) = @mssql_fetch_row($result);
		$a = array();
		preg_match('/^Microsoft *SQL *Server *[^ ]* *- *(\d+\.\d+)/i', $version, $a);
		return ( (isset($a[1]))?(doubleval($a[1])):(0) );
	}
};
?>