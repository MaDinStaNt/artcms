<?php
/**
 * @package Art-cms
 */
/**
 */
require_once("dbal.php");

/**
 * @package Art-cms
 * @ignore
 * @deprecated should be upgraded to use CBaseDB as parent class
 */
class CDataBase extends CDBAL
{
	function CDataBase()
	{
		parent::CDBAL();
	}
	
	function Connect()
	{
		if (!($this->LinkID > 0))
		{
			$this->DebugInfo->Write("CDataBase::Connect()");
			$this->LinkID = @pg_connect("host=".DB_SERVER." port=".DB_PORT." dbname=".DB_DATABASE." user=".DB_USER." password=".DB_PASSWORD);
			if ($this->LinkID)
				return 1;
			else
				$this->DebugInfo->Write("<font color=\"red\">CDataBase::Connect() : failed [".@pg_last_error()."]</font>");
			return 0;
		}
		return $this->LinkID;
	}
	
	function Disconnect()
	{
		$this->DebugInfo->Write("CDataBase::Disconnect()");
		
		if ($this->LinkID > 0) @pg_close($this->LinkID);
		$this->LinkID = -1;
	}
	
	function internalQuery($str)
	{
		if (!$this->Connect()) return 0;
		
		$t1 = get_formatted_microtime();
		$id = @pg_query($this->LinkID, $str);
		$t2 = get_formatted_microtime();
		
		if ($id)
		{
			$af_rows = @pg_affected_rows($id);
			if (!$af_rows)
				$af_rows = @pg_num_rows($id);
			$this->DebugInfo->Write("CDataBase::internalQuery(\$str=".$str.")  time: ".($t2-$t1)." <font color=\"green\">success - ".$af_rows." rows</font>");
		}
		else
		{
			$this->DebugInfo->Write("CDataBase::internalQuery(\$str=".$str.")  <font color=\"red\">failed [".@pg_last_error($this->LinkID)."]</font>");
			$id = false;
		}
		
		return $id;
	}
	
	function get_table_count($TableName, $pk_arr = 0)
	{
		$this->DebugInfo->Write("CDataBase::get_table_count()");
		
		$sql = "select count(*) as cnt from ".DB_PREFIX.$TableName;
		
		$s_fld = "";
		if (is_array($pk_arr))
			foreach ($pk_arr as $k => $v)
				$s_fld .= ( ($s_fld!="")?(" and "):("") ) . $k . "='".pg_escape_string($v)."'";
		if ($s_fld != "")
			$sql .= " where " . $s_fld;

		$tbl_cnt = $this->internalQuery($sql);
		
		if ( ($tbl_cnt) && (pg_num_rows($tbl_cnt) == 1) )
			return pg_result($tbl_cnt, 0, 0);
		else
			return -1;
	}
	
	function GetTableRow($TableName, $pk_arr, $select_fields = 0)
	{
		$this->DebugInfo->Write("CDataBase::GetTableRow()");
		
		
		$s_fld = "";
		foreach ($pk_arr as $k => $v)
			$s_fld .= ( ($s_fld!="")?(" and "):("") ) . $k . "='".pg_escape_string($v)."'";
			
		$sql = "select ";
		if ($select_fields == 0)
			$sql .= "*";
		else
		{
			$o_fld = "";
			foreach ($select_fields as $v)
				$o_fld .= ( ($o_fld!="")?(","):("") ) . $v;
			$sql .= $o_fld;
		}
		$sql .= " from " . DB_PREFIX . $TableName;
		$sql .= " where " . $s_fld;
		
		$tbl_row = $this->internalQuery($sql);
		if ( ($tbl_row) && (pg_num_rows($tbl_row) >= 1) )
			return new CRecordSetRow(pg_fetch_assoc($tbl_row));
		else
			return 0;
	}
	
	function &GetTableRows($TableName, $pk_arr, $or_arr = 0)
	{
		$this->DebugInfo->Write("CDataBase::GetTableRow()");
		
		
		$s_fld = "";
		foreach ($pk_arr as $k => $v)
			$s_fld .= ( ($s_fld=="")?(""):(" and ") ) . $k . "='".pg_escape_string($v)."'";
			
		$sql = "select * from " . DB_PREFIX . $TableName;
		$sql .= " where " . $s_fld;
		$o_fld = "";
		if (is_array($or_arr))
			foreach ($or_arr as $k => $v)
				$o_fld .= ( ($o_fld=="")?(""):(", ") ) . $k . " " . $v;
		if ($o_fld != "")
			$sql .= " order by " . $o_fld;
		$tbl_row = $this->internalQuery($sql);
		if ( ($tbl_row) && (pg_num_rows($tbl_row) >= 1) )
		{
			$rcd = new CRecordSet();
			$f_n = pg_num_fields($tbl_row);
			for ($i=0; $i<$f_n; $i++)
			{
				array_push($rcd->Fields, pg_field_name($tbl_row, $i));
			}
			while ( ($a_arr = pg_fetch_assoc($tbl_row)) )
				$rcd->AddRow($a_arr);

			return $rcd;
		}
		else
			return 0;
	}
	
	function UpdateFromTV($TableName, $tv, $Keys, $up_fld = 0)
	{
		$vl = array();
		$rcd = $this->SelectFirst($TableName);
		foreach ($rcd->Fields as $k => $v)
			if (isset($tv[$v]))
				if ( (!is_array($up_fld)) || ( (is_array($up_fld)) && (in_array($v, $up_fld)) ) )
					$vl[$v] = $tv[$v];
		return $this->Update($TableName, $vl, $Keys);
	}
	// example to use query update table set f1=v1 and f2=v2 where f3=v3 and f4=v4
	// run this procedure
	// update("table", array("f1"=>"v1", "f2"=>"v2"), array("f3"=>"v3", "f4"=>"v4"));
	function Update($TableName, $UpdateFields, $Keys)
	{
		$this->DebugInfo->Write("CDataBase::Update()");

		
		
		$sql = "update " . DB_PREFIX . $TableName . " set ";
		$c = "";
		foreach ($UpdateFields as $k => $v)
		{
			$sql .= $c . $k . "='".pg_escape_string($v)."'";
			$c = ", ";
		}
		
		$sql .= " where ";
		$c = "";
		foreach ($Keys as $k => $v)
		{
			$sql .= $c . $k . "='".pg_escape_string($v)."'";
			$c = " and ";
		}
		
		$this->internalQuery($sql);
	}

	function InsertFromTV($TableName, $tv)
	{
		$vl = array();
		$rcd = $this->SelectFirst($TableName);
		foreach ($rcd->Fields as $k => $v)
			if (isset($tv[$v]))
				$vl[$v] = $tv[$v];
		
		return $this->Insert($TableName, $vl);
	}
	
	function Insert($TableName, $values)
	{
		
		
		$sql = "insert into " . DB_PREFIX . $TableName . " (";
		$c = "";
		foreach ($values as $k => $v)
		{
			$sql .= $c . $k;
			$c = ", ";
		}
		
		$sql .= ") values(";
		$c = "";
		foreach ($values as $k => $v)
		{
			if (is_null($v))
				$sql .= $c;
			elseif ($v != "now()")
				$sql .= $c . "'".pg_escape_string($v)."'";
			else
				$sql .= $c . $v;
			$c = ", ";
		}
		$sql .= ")";
		
		if (!$this->internalQuery($sql))
			return false;
		
		$lid =  $this->get_last_id();
		$this->DebugInfo->Write("Insert returns: ". $lid);
		return $lid;
	}
	
	function get_last_id()
	{
		return pg_insert_id($this->LinkID);
	}
	
	function delete_sql($TableName, $pk_arr)
	{
		$s_fld = "";
		foreach ($pk_arr as $k => $v)
			$s_fld .= ( ($s_fld=="")?(""):(" and ") ) . $k . "='".pg_escape_string($v)."'";
			
		$sql = "delete from " . DB_PREFIX . $TableName;
		$sql .= " where " . $s_fld;
		
		return $this->internalQuery($sql);
	}

	function SelectFirst($TableName, $limit = 1)
	{
		$this->DebugInfo->Write("CDataBase::SelectFirst()");
		
		$sql = "select * from " . DB_PREFIX . $TableName . " limit ".$limit;
		$tbl_row = $this->internalQuery($sql);
		if ($tbl_row)
		{
			$rcd = new CRecordSet();
			$f_n = pg_num_fields($tbl_row);
			for ($i=0; $i<$f_n; $i++)
			{
				array_push($rcd->Fields, pg_field_name($tbl_row, $i));
			}
			while ( ($a_arr = pg_fetch_assoc($tbl_row)) )
				$rcd->AddRow($a_arr);
			pg_free_result($tbl_row);
			return $rcd;
		}
		else
			return 0;
	}
	
	function get_product_quantity($ProductID)
	{
		$rcd = $this->GetTableRow("pr_products", array("id_product" => $ProductID));
		if ($rcd)
			return $rcd->get_field("i_count");
		else
			return -1;
	}

	function select_sql($sql)
	{
		$this->DebugInfo->Write("CDataBase::SelectSQL($sql)");

		
		$sql = str_replace("%prefix%", DB_PREFIX, $sql);
		
		$tbl_row = $this->internalQuery($sql);
		
		if ($tbl_row)
		{
			$rcd = new CRecordSet();
			$f_n = pg_num_fields($tbl_row);
			for ($i=0; $i<$f_n; $i++)
			{
				array_push($rcd->Fields, pg_field_name($tbl_row, $i));
			}
			while ( ($a_arr = pg_fetch_assoc($tbl_row)) )
				$rcd->AddRow($a_arr);
				
			pg_free_result($tbl_row);
			return $rcd;
		}
		else
			return 0;
	}
	
	function SelectFromTable($TableName)
	{
		$this->DebugInfo->Write("CDataBase::SelectFromTable()");

		$page = InGetPost("page", 0);
		$ipp = InGetPost("ipp", 10);
		//$ppp = InGetPost("ppp", 10);
		
		
		$sql = "select * from " . DB_PREFIX . $TableName . " limit ".($page*$ipp).",".$ipp;
		
		$tbl_row = $this->internalQuery($sql);
		
		if ($tbl_row)
		{
			$rcd = new CRecordSet();
			$f_n = pg_num_fields($tbl_row);
			for ($i=0; $i<$f_n; $i++)
			{
				array_push($rcd->Fields, pg_field_name($tbl_row, $i));
			}
			while ( ($a_arr = pg_fetch_assoc($tbl_row)) )
				$rcd->AddRow($a_arr);
				
			pg_free_result($tbl_row);
			return $rcd;
		}
		else
			return 0;
	}
	
	function get_whole_table($TableName, $ord_class = 0, $add_table_prefix = true, $pk_class = 0, $add_limit = false)
	{
		$this->DebugInfo->Write("CDataBase::get_whole_table()");

		$page = InGetPost("page", 0);
		$ipp = InGetPost("ipp", 10);

		
		$sql = "select * from " . ( ($add_table_prefix)?(DB_PREFIX):("") ) . $TableName;
		if (is_array($pk_class))
		{
			$s_fld = "";
			foreach ($pk_class as $k => $v)
				$s_fld .= ( ($s_fld!="")?(" and "):("") ) . $k . "='".pg_escape_string($v)."'";
				
			if ($s_fld != "")
				$sql .= " where " . $s_fld;
		}
		if (is_array($ord_class))
		{
			$sql .= " order by ";
			$c = "";
			foreach ($ord_class as $k => $v)
			{
				$sql .= $c . $k . " " . $v;
				$c = ",";
			}
		}
		
		if ($add_limit)
			$sql .= " limit ".($page*$ipp).",".$ipp;
		$tbl_row = $this->internalQuery($sql);
		
		if ($tbl_row)
		{
			$rcd = new CRecordSet();
			$f_n = pg_num_fields($tbl_row);
			for ($i=0; $i<$f_n; $i++)
			{
				array_push($rcd->Fields, pg_field_name($tbl_row, $i));
			}
			while ( ($a_arr = pg_fetch_assoc($tbl_row)) )
				$rcd->AddRow($a_arr);
				
			pg_free_result($tbl_row);
			return $rcd;
		}
		else
			return 0;
	}

}
?>