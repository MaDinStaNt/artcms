<?php
/**
 * @package Art-cms
 */
/**
 */
require_once('_db.php');

/**
--------------------------------------------------------------------------------
Class CDataBase v 1.0
MySQL DataBase class
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
 * @package Art-cms
 * @ignore
 */
class CDataBase extends CBaseDB
{
        function CDataBase(&$app, $locale = 'en_US')
        {
                parent::CBaseDB($app);
                $this->now_stmt = 'now()';
                $this->datetime_stmt = 'DATETIME';
                $this->clob_stmt = 'TEXT';
                $this->auto_inc_stmt = 'auto_increment';
                $this->concat_func_stmt = 'concat';
                $this->concat_char = ',';
                $this->locale = $locale;

                if (!function_exists('mysql_connect'))
                        system_die('MySQL module is not installed');
        }
        // _set_limit
        function _set_limit($sql, $limit)
        {
                if (!is_null($limit))
                        if (!is_array($limit))
                                $sql.= $limit> 0 ? ' LIMIT '.intval($limit):'';
                        elseif (isset($limit[1]) && isset($limit[0]) && ($limit[1] > 0) && ($limit[0] > 0))
                                $sql.=' LIMIT '.((intval($limit[0])-1) * intval($limit[1])). ',' . intval($limit[1]);
                return $sql;
        }
        // free
        function free($id)
        {
                return @mysql_free_result($id);
        }
        // get_last_id
        function get_last_id()
        {
                return @mysql_insert_id($this->LinkID);
        }
        // internalConnect
        function internalConnect()
        {
                if ($this->LinkID === false)
                {
                        $this->LinkID = @mysql_connect(((isset($this->DB_SERVER))?($this->DB_SERVER):(DB_SERVER)).':'.DB_PORT, ((isset($this->DB_USER))?($this->DB_USER):(DB_USER)), ((isset($this->DB_PASSWORD))?($this->DB_PASSWORD):(DB_PASSWORD)), true);
                        if ($this->LinkID !== false)
                        {
                                @mysql_query("set names 'utf8'", $this->LinkID);
                                @mysql_query("set collation_connection='utf8_general_ci'", $this->LinkID);
                                @mysql_query("set collation_server='utf8_general_ci'", $this->LinkID);

                                @mysql_query("set character_set_client='utf8_general_ci'", $this->LinkID);
                                @mysql_query("set character_set_connection='utf8_general_ci'", $this->LinkID);
                                @mysql_query("set character_set_results='utf8_general_ci'", $this->LinkID);
                                @mysql_query("set character_set_server='utf8_general_ci'", $this->LinkID);
								@mysql_query("SET lc_time_names = '".$this->locale."'", $this->LinkID);

                                if (@mysql_select_db(((isset($this->DB_DATABASE))?($this->DB_DATABASE):(DB_DATABASE)), $this->LinkID))
                                        return $this->LinkID;
                                else
                                {
                                        @mysql_query('create database `'.((isset($this->DB_DATABASE))?($this->DB_DATABASE):(DB_DATABASE)).'`', $this->LinkID);
                                        if (@mysql_select_db(((isset($this->DB_DATABASE))?($this->DB_DATABASE):(DB_DATABASE)), $this->LinkID))
                                                return $this->LinkID;
                                        else
                                                $this->last_error = 'Cannot select DataBase';
                                }
                        }
                        else
                                $this->last_error = 'Cannot connect to DataBase server';
                        return false;
                }
                return $this->LinkID;
        }
        // internalDisconnect
        function internalDisconnect()
        {
                if ($this->LinkID > 0) @mysql_close($this->LinkID);
                $this->LinkID = false;
        }
        // internalEscape
        function internalEscape($s)
        {
                $this->last_error = '';
                if ( ($this->LinkID === false) && (!$this->internalConnect()) ) return false;
                if (function_exists('mysql_real_escape_string'))
                        return @mysql_real_escape_string($s, $this->LinkID);
                else
                        return @mysql_escape_string($s);
        }
        // internalLikeEscape
        function internalLikeEscape($s)
        {
                $this->last_error = '';
                if ( ($this->LinkID === false) && (!$this->internalConnect()) ) return false;
                if (function_exists('mysql_real_escape_string'))
                        $s = @mysql_real_escape_string($s, $this->LinkID);
                else
                        $s = @mysql_escape_string($s);
                $s = str_replace(array('\\', '%', '_'), array('\\\\', '\\%', '\\_'), $s);
                return $s;
        }
        function internalFetchRow($r)
        {
                return @mssql_fetch_array($r, MYSQL_NUM);
        }
        // internalFetchArray
        function internalFetchArray($r)
        {
                return @mysql_fetch_array($r, MYSQL_BOTH);
        }
        // internalFetchAssoc
        function internalFetchAssoc($r)
        {
                return @mysql_fetch_array($r, MYSQL_ASSOC);
        }
        // internalNumRows
        function internalNumRows($r)
        {
                return @mysql_num_rows($r);
        }
        // internalFetchField
        function internalFetchField($r, $i)
        {
                return @mysql_fetch_field($r, $i);
        }
        // internalNumFields
        function internalNumFields($r)
        {
                return @mysql_num_fields($r);
        }
        // internalQuery
        function internalQuery($str)
        {
                $this->last_error = '';
                if ( ($this->LinkID === false) && (!$this->internalConnect()) ) return false;
                $str = str_replace('%prefix%', ((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX), $str);
                //$str = str_replace('%dmy%', '%m/%d/%Y', $str);
                //$str = str_replace('%dmyhm%', '%m/%d/%Y %H:%i', $str);
                if (!$GLOBALS['DebugLevel'])
                        return @mysql_query($str, $this->LinkID);
                else
                {
                        $t1 = get_formatted_microtime();
                        $id = @mysql_query($str, $this->LinkID);
                        $t2 = get_formatted_microtime();
                        if ($id)
                        {
                                $dt = ($t2-$t1);
                                if ($dt < 0.1)
                                        $GLOBALS['GlobalDebugInfo']->Write(htmlspecialchars($str) . ' time: '.$dt.' <font color="green">success - '.@mysql_affected_rows($this->LinkID).' rows</font>');
                                else
                                        $GLOBALS['GlobalDebugInfo']->Write(htmlspecialchars($str) . ' time: <font color="red">'.$dt.'</font> <font color="green">success - '.@mysql_affected_rows($this->LinkID).' rows</font>');
                        }
                        else
                        {
                                $this->last_error = 'Cannot perfom SQL query: '.$str . ' '. @mysql_error($this->LinkID);
                                $GLOBALS['GlobalDebugInfo']->Write(htmlspecialchars($str) . ' <font color="red">failed ['.@mysql_error($this->LinkID).']</font>');
                                system_die($this->last_error);
                        }
                        return $id;
                }
        }
        // lock
        function lock($tables=null)
        {
                $t = '';
                if (is_array($tables))
                        foreach ($tables as $k => $v)
                                $t .= ( (($t!='')?(','):('')) . '`%prefix%' . $v . '` WRITE' );
                return $this->custom_sql('LOCK TABLES '.$t);
        }
        // unlock
        function unlock()
        {
                $this->custom_sql('UNLOCK TABLES');
        }
        function is_table($TableName)
        {
                return (mysql_num_rows($this->internalQuery('SHOW TABLES FROM `'.((isset($this->DB_DATABASE))?($this->DB_DATABASE):(DB_DATABASE)).'` like \''.((isset($this->DB_PREFIX))?$this->DB_PREFIX:DB_PREFIX).$TableName.'\''))==1);
        }
        // version
        function version()
        {
                $this->last_error = '';
                if ( ($this->LinkID === false) && (!$this->internalConnect()) ) return false;
                $mysql_version = @mysql_get_server_info($this->LinkID);
                if ($mysql_version===false) return false;
                $a = array();
                preg_match('/^(\d+\.\d+)/', $mysql_version, $a);
                return ( (isset($a[1]))?(doubleval($a[1])):(0) );
        }
}
?>