<?php
/**
* @package      fwolflib
* @copyright    Copyright 2006, Fwolf
* @author       Fwolf <fwolf.aide@gmail.com>
*/

require_once('adodb/adodb.inc.php');

/**
* Data backup tool, result is sql format.
* Test under sybase 11.92 ok.
*
* @package    fwolflib
* @copyright  Copyright 2006, Fwolf
* @author     Fwolf <fwolf.aide@gmail.com>
* @since      2006-11-14
* @access     public
* @version    $Id$
*/
class DbBak2Sql
{
	/**
	 * Charset of database
	 * If charset of db diff from os, do convert when execute sql.
	 * @var	string
	 * @access	public
	 */
	var $mCharsetDb = '';

	/**
	 * Charset of operation system
	 * @var string
	 * @access	public
	 * @see	$mCharsetDb
	 */
	var $mCharsetOs = '';
	
	/**
	 * Db connection object
	 * @var object
	 * @access	private
	 */
	var $mDb;

	/**
	 * Ignore columns
	 * These columns will not be trans to sql when processed.
	 * @var	array
	 * @access	public
	 */
	var $mIgnoreColumn = array('lasttime');

	/**
	 * Log file
	 * Log file to write, this is only filename, path is $this->mTargetPath
	 * @var	string
	 * @access	public
	 * @see	$mTargetPath
	 */
	var $mLogFile = 'dbbak2sql.log';
	
	/**
	 * Db server information array
	 * 	Array item: host, user, pass, name, type.
	 * @var	array
	 * @access	private
	 */
	var $mServer = array();

	/**
	 * Summary text
	 * @var string
	 * @access public
	 */
	var $mSummary = '';

	/**
	 * Tables will be backup
	 * Include needed, exclude no needed, this is result.
	 * @var	array
	 * @access	private
	 */
	var $mTable = array();

	/**
	 * Tables to be exclude from backup task
	 * @var array
	 * @access	private
	 */
	var $mTableExclude = array();

	/**
	 * Table need to be group by some cols when backup
	 * Usually used when table contains too much rows
	 * @var	array
	 * @access	private
	 */
	var $mTableGroupby = array();

	/**
	 * Tables to be include in backup task
	 * If not empty, will only backup tables in this list.
	 * @var array
	 * @access	private
	 */
	var $mTableInclude = array();

	/**
	 * Where to save exported sql files.
	 * @var	string
	 * @access	private
	 */
	var $mTargetPath = '/tmp/dbbak2sql';


	/**
	 * Construct function
	 * @access public
	 * @param	array	$server	Db server information
	 */
	function __construct($server=array())
	{
		if (!empty($server))
			$this->SetDatabase($server);
	} // end of func construct
	  

	/**
	 * Do backup to database
	 * @access	public
	 */
	public function Bak()
	{
		file_put_contents($this->mTargetPath . '/' . $this->mLogFile, '');
		$this->GetTableList();
		foreach ($this->mTable as $tbl)
			$this->BakTable($tbl);
		$this->Summary();
	} // end of func Bak

	
	/**
	 * Do backup to a single table
	 * @access	private
	 * @param	string	$tbl Table name
	 */
	private function BakTable($tbl)
	{
		// Clear sql file(sql is save to seperated file, not need clear anymore
		//file_put_contents($this->mTargetPath . "/$tbl.sql", '');
		$this->Log("Begin to backup $tbl, ");
		
		$cols = $this->GetTblFields($tbl);
		
		// Split sql to 10000 rows per step
		$sql_step = 10000;
		$sql_offset = 0;
		// Rows and Byte count
		$done_rows = 0;
		$done_bytes = 0;
		
		// Get total rows
		$sql = "select count(1) as c from $tbl";
		$rs = $this->mDb->Execute($sql);
		$rowcount = $rs->fields['c'];
		$this->Log("Got $rowcount rows: ");
		
		// Write rs to sql
		// GetInsertSQL failed for unknown reason, manual generate sql
		//$sql = $this->mDb->GetInsertSQL($rs, $cols, false, false);
		$bakfile = $this->mTargetPath . "/$tbl.sql";
		$sql = "truncate table $tbl;\n";
		if (true == $this->NeedIdentityInsert())
			$sql .= "set identity_insert $tbl on;\n";
		
		// Backup by groupby will cause two situation:
		// 1. one sql file will contain rows diffs with sql_step.
		// 2. sql file saved's number sometimes is not continued.
		
		// Groupby rules is converted to where clauses
		$ar_where = $this->GroupbyRule2WhereSql($tbl);
		while ($sql_offset < $rowcount)
		{
			$this->Log(".");
			// Execute sql
			// When ar_where is empty, the loop should be end.
			// Or groupby is not used.
			if (!empty($ar_where))
			{
				$s_where = array_shift($ar_where);
				$sql_select = "select * from $tbl $s_where";
				$rs = $this->mDb->Execute($sql_select);
			}
			else
			{
				$sql_select = "select * from $tbl";
				// This select sql does not need iconv
				//if ($this->mCharsetDb != $this->mCharsetOs)
				//	$sql = mb_convert_encoding($sql, $this->mCharsetDb, $this->mCharsetOs);
				$rs = $this->mDb->SelectLimit($sql_select, $sql_step, $sql_offset);
			}
			$rs_rows = $rs->RecordCount();
			if (0 != $this->mDb->ErrorNo())
				$this->Log("\n" . $db->ErrorMsg() . "\n");
			else
			{
				$sql .= $this->Rs2Sql($rs, $tbl, $cols);
				$done_rows += $rs_rows;
			}

			// Save this step to file
			if ($this->mCharsetDb != $this->mCharsetOs)
				$sql = mb_convert_encoding($sql, $this->mCharsetOs, $this->mCharsetDb);
			// Save to seperated file, first check about how many files will be used.
			if ($rowcount > $sql_step)
			{
				$i = strlen(strval(ceil($rowcount / $sql_step)));
				$s = substr(str_repeat('0', $i) . strval(floor($sql_offset / $sql_step)), $i * -1) . '.';
			}
			else
				$s = '';
			$bakfile = $this->mTargetPath . "/$tbl.${s}sql";
			file_put_contents($bakfile, $sql, FILE_APPEND);
			// Prepare for loop
			$done_bytes += strlen($sql);
			unset($sql);
			$sql = '';
			//$sql_offset += $sql_step;
			$sql_offset += $rs_rows;
			unset($rs);
		}

		// End sql, the last one.
		if (true == $this->NeedIdentityInsert())
			$sql .= "set identity_insert $tbl off;\n";
		file_put_contents($bakfile, $sql, FILE_APPEND);
				
		$this->Log("Saved $done_rows rows, Total size: $done_bytes bytes.\n");
		
	} // end of func BakTable


	/**
	 * 获得数据库连接
	 * @access	private
	 * @param	array	$server
	 * @return object
	 */
	private function &DbConn($server)
	{
		global $ADODB_FETCH_MODE;
		
		//ADODB设定
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		
		try
		{
			$conn = &ADONewConnection($server['type']);
			//$conn->debug = true;
			$conn->Connect($server['host'],
				$server['user'],
				$server['pass'],
				$server['name']);
			//针对mysql 4.1以上，UTF8编码的数据库，需要在连接后指定编码
			//$conn->Execute('set names "utf8"');
			if ('mysql' == $server['type']) $conn->Execute('set names "utf8"');
		}
		catch (Exception $e)
		{
			//var_dump($e);
			adodb_backtrace($e->getTrace());
			//echo $e;
			exit();
		}
		return $conn;
	} // end of func DbConn


	/**
	 * Retrieve table list from db
	 * @access	private
	 */
	private function GetTableList()
	{
		if (!empty($this->mTableInclude))
			$this->mTable = $this->mTableInclude;
		else
			// Adodb 4.65 can't read sysbase table list currect,
			// Replaced it with 4.93a, it's fine.
			$this->mTable = $this->mDb->MetaTables('TABLES');

		// Compute exclude
		foreach ($this->mTableExclude as $tbl)
		{
			$idx = array_search($tbl, $this->mTable);
			if (!(false === $idx))
				unset($this->mTable[$idx]);
		}

		// Write Log
		$this->Log("Ready for backup " . count($this->mTable) . " tables.\n");
	} // end of func GetTableList


	/**
	 * Get fields of a table, ignore prefered fields
	 * @access	private
	 * @param	string	$tbl
	 * @return	array
	 */
	private function GetTblFields($tbl)
	{
		$rs_cols = $this->mDb->MetaColumns($tbl, false);
		//print_r($rs_cols);
		$cols = array();
		if (!empty($rs_cols))
			foreach ($rs_cols as $c)
			{
				// Ignore some columns ?(timestamp)
				if (false == in_array($c->name, $this->mIgnoreColumn))
					array_push($cols, $c->name);
			}
		return $cols;
	} // end of func GetTblFields


	/**
	 * Convert groupby rules to where sql clauses
	 * Retrieve data from db by groupby rules, and convert to where sql.
	 * Used when backup, where clauses can used directly in select sql
	 * @access	private
	 * @param	string	$tbl
	 * @return	array
	 */
	private function GroupbyRule2WhereSql($tbl)
	{
		$ar_where = array();
		if (!empty($this->mTableGroupby[$tbl]))
		{
			$groupby = $this->mTableGroupby[$tbl];
			$sql = "select distinct $groupby from $tbl";
			$rs = $this->mDb->Execute($sql);
			
			// Convert every rows to where sql
			$cols = explode(',', $groupby);
			$rs_cols = $this->mDb->MetaColumns($tbl, false);
			while (!empty($rs) && !$rs->EOF && !empty($cols))
			{
				$sql = ' WHERE 1=1 ';
				foreach ($cols as $c)
				{
					$val = $this->ParseSqlData($rs->fields[$c], $rs_cols[strtoupper($c)]->type);
					$sql .= " and $c=$val ";
				}
				array_push($ar_where, $sql);
				$rs->MoveNext();
			}
		}
		return $ar_where;
	} // end of function GroupbyRule2WhereSql


	/**
	 * Save log
	 * Both log file and summary text is saved to.
	 * @access	private
	 * @param	string	$log
	 */
	private function Log($log)
	{
		$logfile = $this->mTargetPath . '/'  . $this->mLogFile;
		file_put_contents($logfile, $log, FILE_APPEND);
		echo $log;
		$this->mSummary .= $log;
	} // end of func Log
	
	
	/**
	 * Determin if current db driver need set identity_insert tbl on/off
	 * @access	private
	 * @return	boolean
	 */
	private function NeedIdentityInsert()
	{
		$ar_need = array('mssql', 'sybase', 'sybase_ase');
		if (true == in_array($this->mServer['type'], $ar_need))
			return true;
		else
			return false;
	} // end of func NeedIdentityInsert


	/**
	 * Parse sql text used in sql value field
	 * @access	private
	 * @param	string	$val
	 * @param	string	$type
	 * @return	string
	 */
	private function ParseSqlData($val, $type)
	{
		// First, ' -> '' in sybase
		//if (true == in_array($this->mServer['type'], array('sybase', 'sybase_ase')))
		//	$val = str_replace("'", "''", $val);
		// Quote fields of char
		$varchar = array('char', 'charn', 'text', 'varchar', 'varchar2', 'varcharn');
		if (true == in_array($type, $varchar))
			$val = '"' . addslashes($val) . '"';
		
		// Datetime field
		$datestyle = array('date', 'daten', 'datetime', 'datetimn');
		if (in_array($type, $datestyle))
			if (empty($val))
				$val = 'null';
			else
				$val = '"' . $val . '"';
		
		// If a numeric field is null
		if (!in_array($type, $varchar) && !in_array($type, $datestyle) && is_null($val))
			$val = 'null';

		return $val;
	} // end of func ParseSqlData


	/**
	 * Convert ADOdb recordset to sql text
	 * @access	private
	 * @param	object	$rs
	 * @param	string	$tbl
	 * @param	array	$cols
	 * @return	string
	 */
	private function Rs2Sql(&$rs, $tbl, $cols=array())
	{
		if (empty($rs) || $rs->EOF)
			return '';
		else
			{
				$sql = '';
				if (empty($cols))
					$cols = $this->GetTblFields($tbl);
				$rs_cols = $this->mDb->MetaColumns($tbl, false);
			
				while (!$rs->EOF)
				{
					// Insert sql begin
					$sql_i = "INSERT INTO $tbl (" . implode(',', $cols) . " ) VALUES ( \n";
					// Fields data
					$ar = array();
					foreach ($cols as $c)
					{
						$val = $rs->fields[$c];
						$type = $rs_cols[strtoupper($c)]->type;
						array_push($ar, $this->ParseSqlData($val, $type));
					}
					$sql_i .= implode(',', $ar) . "\n";
					// Insert sql end
					$sql_i .= ");\n";
					$sql .= $sql_i;
					// Move cursor
					$rs->MoveNext();
				}
			}
		return $sql;
	} // end of func Rs2Sql

	
	/**
	 * Accept database information from outside class
	 *	Didnot validate data send in.
	 *	And connect to db after store infomation.
	 * @access	public
	 * @var	array	$server	array items: host, user, pass, name, type
	 */
	public function SetDatabase($server)
	{
		if (!empty($server) && is_array($server))
		{
			$this->mServer = $server;
			$this->mDb = $this->DbConn($this->mServer);
		}
	} // end of func SetDatabase


	/**
	 * Set tables will not be backup
	 * @access public
	 * @var	array	$ar
	 */
	public function SetTableExclude($ar)
	{
		if (!empty($ar) and is_array($ar))
		{
			$this->mTableExclude = $ar;
		}
	} // end of func SetTableExclude


	/**
	 * Set table group by rules when backup-select
	 * If given cols is empty, it will remove tbl from list need-to-be groupby.
	 * Multi cols can be assigned split by ','.
	 * @access	public
	 * @var	string	$tbl
	 * @var	string	$cols
	 */
	public function SetTableGroupby($tbl, $cols)
	{
		if (empty($cols))
			unset($this->mTableGroupby[$tbl]);
		else
			$this->mTableGroupby[$tbl] = $cols;
	} // end of func SetTableGroupby


	/**
	 * Set tables will only be backup
	 * @access public
	 * @var	array	$ar
	 */
	public function SetTableInclude($ar)
	{
		if (!empty($ar) and is_array($ar))
		{
			$this->mTableInclude = $ar;
		}
	} // end of func SetTableInclude


	/**
	 * Set where to save sql files exported.
	 * If directory doesn't exists, create it.
	 * @access	public
	 * @var	string	$path
	 */
	public function SetTargetPath($path)
	{
		$this->mTargetPath = $path;
		// Check and create
		if (file_exists($path) && !is_dir($path))
			die("Target path is a file.");
		elseif (!file_exists($path))
			mkdir($path, 0700, true);	// Do path end with '/' needed ?
	} // end of func SetTargetPath

	/**
	 * Print or write summary text of the whole backup process
	 * @access	private
	 */
	private function Summary()
	{
		echo $this->mSummary . "\n";
	} // end of func Summary

	
} // end of class DbBak2Sql
/*
$db = DbConn();

//打印各市地区编码
$sql[2] = 'select substring(bm, 1, 4), mc from dqk where bm like "%00"';

$sql = $sql[4];
$sql = mb_convert_encoding($sql, 'gbk', 'utf8');
$rs = $db->Execute($sql);
if (0 != $db->ErrorNo())
	echo $db->ErrorMsg();
$s = '';
$ar = $rs->GetArray();

//输出方式一，直接print
//$s = print_r($ar, true);
//输出方式二，tab间隔，便于导入
foreach ($ar as $key=>$row)
{
	foreach ($row as $var)
		$s .= "$var\t";
	$s .= "\n";
}

$s = mb_convert_encoding($s, 'utf8', 'gbk');
echo $s;
*/
?>
