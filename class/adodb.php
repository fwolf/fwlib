<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2008-04-22
 * @version		$Id$
 */

// Set include path in __construct
//require_once('adodb/adodb.inc.php');
require_once('fwolflib/class/sql_generator.php');

/**
 * Extended ADODB class
 * 
 * Include all ADODB had, and add a little others.
 * 
 * Piror use this class' method and property, it the get/set/call target
 * is not exists, use original ADODB's, this can be done by php's mechematic
 * of overload __call __get __set.
 * 
 * 这似乎是extend ADODB的一种比较好的方式，比官方网站文档上给的按不同数据库来继承子类的方式，我认为要更方便一些。缺点是没有对RecordSet对象进行处理。
 * 
 * 
 * 执行sql查询的系列更改中，限定系统/HTML/PHP使用$sSysCharset指定的编码，涉及函数列在__call()中，但一些通过数组等其它方式传递参数的ADODB方法仍然无法通过这种方式实现sql编码自动转换。
 * 
 * 执行返回的数据还是需要转码的，不过返回数据的种类太多，放在应用中实现更简单一些，这里不自动执行，只提供一个EncodingConvert方法供用户调用。
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2008-04-08
 * @version		$Id$
 */
class Adodb
{
	/**
	 * Real ADODB connection object
	 * @var object
	 */
	protected $__conn = null;
	
	/**
	 * Db profile
	 * @var array
	 */
	public $aDbProfile = null;
	
	/**
	 * Table schema
	 * @var array
	 */
	public $aMetaColumns = array();
	
	/**
	 * Sql generator object
	 * @var object
	 */
	protected $oSg;
	
	/**
	 * System charset
	 * 
	 * In common, this is your php script/operation system charset
	 * @var string
	 */
	public $sSysCharset = 'utf8';
	
	
	/**
	 * construct
	 * 
	 * <code>
	 * $dbprofile = array(type, host, user, pass, name, lang);
	 * type is mysql/sybase_ase etc,
	 * name is dbname to select,
	 * lang is db server charset.
	 * </code>
	 * @var param	array	$dbprofile
	 * @var param	string	$path_adodb		Include path of original ADODB
	 */
	public function __construct($dbprofile, $path_adodb = '')
	{
		// Include original adodb lib
		if (empty($path_adodb))
			$path_adodb = 'adodb/adodb.inc.php';
		require_once($path_adodb);
		
		$this->aDbProfile = $dbprofile;
		$this->__conn = & ADONewConnection($dbprofile['type']);
		
		// Sql generator object
		$this->oSg = new SqlGenerator($this);
	} // end of class __construct
	
	
	/**
	 * Overload __call, redirect method call to adodb
	 * @var string	$name	Method name
	 * @var array	$arg	Method argument
	 * @global	int	$i_db_query_times
	 * @return mixed
	 */
	public function __call($name, $arg)
	{
		// Before call, convert $sql encoding first
		if ($this->sSysCharset != $this->aDbProfile['lang'])
		{
			// Method list by ADODB doc order
			// $sql is the 1st param
			if (in_array($name, array('Execute',
									  'CacheExecute',
									  'SelectLimit',
									  'CacheSelectLimit',
									  'Prepare',
									  'PrepareSP',
									  'GetOne',
									  'GetRow',
									  'GetAll',
									  'GetCol',
									  'CacheGetOne',
									  'CacheGetRow',
									  'CacheGetAll',
									  'CacheGetCol',
									  'GetAssoc',
									  'CacheGetAssoc',
									  'ExecuteCursor',
									)))
				$arg[0] = mb_convert_encoding($arg[0], $this->aDbProfile['lang'], $this->sSysCharset);
			
			// $sql is the 2nd param
			if (in_array($name, array('CacheExecute',
									  'CacheSelectLimit',
									  'CacheGetOne',
									  'CacheGetRow',
									  'CacheGetAll',
									  'CacheGetCol',
									  'CacheGetAssoc',
									)))
				$arg[1] = mb_convert_encoding($arg[1], $this->aDbProfile['lang'], $this->sSysCharset);
		}
		
		// Count db query times
		// Use global var so multi Adodb object can be included in count.
		//	(Done in func now)
		// Use standalone func to can be easy extend by sub class.
		if (in_array($name, array(
			'Execute', 'SelectLimit', 'GetOne', 'GetRow', 'GetAll',
			'GetCol', 'GetAssoc', 'ExecuteCursor'
			)))
			$this->CountDbQueryTimes();
		
		return call_user_func_array(array($this->__conn, $name), $arg);
	} // end of func __call
	
	
	/**
	 * Overload __get, redirect method call to adodb
	 * @param string	$name
	 * @return mixed
	 */
	public function __get($name)
	{
		return $this->__conn->$name;
	} // end of func __get
	
	
	/**
	 * Overload __set, redirect method call to adodb
	 * @param string	$name
	 * @param mixed		$val
	 */
	public function __set($name, $val)
	{
		$this->__conn->$name = $val;
	} // end of func __set
	
	
	/**
}
	 * Connect, Add mysql 'set names utf8'
	 * 
	 * <code>
	 * Obmit params(dbprofile was set in __construct):
	 * param $argHostname		Host to connect to
	 * param $argUsername		Userid to login
	 * param $argPassword		Associated password
	 * param $argDatabaseName	database
	 * </code>
	 * @param $forcenew			Force new connection
	 * @return boolean
	 */
	public function Connect($forcenew = false)
	{
		try
		{
			// Sybase will echo 'change to master' warning msg
			// :THINK: Will this problem solved if we drop default
			// database master from sa user ?
			if ($this->IsDbSybase())
				$rs = @$this->__conn->Connect($this->aDbProfile['host'], 
										 $this->aDbProfile['user'], 
										 $this->aDbProfile['pass'], 
										 $this->aDbProfile['name'], 
										 $forcenew);
			else 
				$rs = $this->__conn->Connect($this->aDbProfile['host'], 
										 $this->aDbProfile['user'], 
										 $this->aDbProfile['pass'], 
										 $this->aDbProfile['name'], 
										 $forcenew);
			
			// 针对mysql 4.1以上，UTF8编码的数据库，需要在连接后指定编码
			// Can also use $this->aDbProfile['type']
			// mysql, mysqli
			if ($this->IsDbMysql())
				$this->__conn->Execute('set names "' . $this->aDbProfile['lang'] . '"');
		}
		catch (Exception $e)
		{
			//var_dump($e);
			adodb_backtrace($e->getTrace());
			//echo $e;
			exit();
		}
		
		return $rs;
	} // end of func Connect
	
	
	/**
	 * Count how many db query have executed
	 * 
	 * This function can be extend by subclass if you want to count on multi db objects.
	 * 
	 * Can't count in Adodb::property, because need display is done by Controler,
	 * which will call View, but Adodb is property of Module,
	 * so we can only use global vars to save this value. 
	 * @global	int	$i_db_query_times
	 */
	protected function CountDbQueryTimes() {
		global $i_db_query_times;
		$i_db_query_times ++;
	} // end of func CountDbQueryTimes
	
	
	/**
	 * Convert recordset(simple array) or other string
	 * from db encoding to system encoding
	 * 
	 * Use recursive mechanism, beware of loop hole.
	 * @param mixed	&$s	Source to convert
	 * @return mixed
	 */
	public function EncodingConvert(&$s)
	{
		if (is_array($s) && !empty($s))
			foreach ($s as &$val)
				$this->EncodingConvert($val);
		
		if (is_string($s))
		{
			if ($this->sSysCharset != $this->aDbProfile['lang'])
				$s = mb_convert_encoding($s, $this->sSysCharset, $this->aDbProfile['lang']);
		}
		return $s;
	} // end of func EncodingConvert
	
	
	/**
	 * Generate SQL statement
	 * 
	 * User should avoid use SELECT/UPDATE/INSERT/DELETE simultaneously.
	 * 
	 * Generate order by SQL statement format order.
	 * 
	 * UPDATE/INSERT/DELETE is followed by [TBL_NAME], 
	 * so need not use FROM.
	 * @param array	$ar_sql	Array(select=>..., from=>...)
	 * @return string
	 * @see	SqlGenerator
	 */
	public function GenSql($ar_sql)
	{
		// Changed to use SqlGenerator
		if (!empty($ar_sql))
		{
			return $this->oSg->GetSql($ar_sql);
		}
	} // end of func GenSql
	
	
	/**
	 * Get table schema
	 * @param	string	$table
	 * @param	boolean	$forcenew	Force to retrieve instead of read from cache
	 * @return	array
	 * @see $aMetaColumns
	 */
	public function GetMetaColumns($table, $forcenew = false)
	{
		if (!isset($this->aMetaColumns[$table]) || (true == $forcenew))
		{
			$this->aMetaColumns[$table] = $this->MetaColumns($table);
			
			// Convert columns to native case
			$col_name = $this->MetaColumnNames($table);
			// $col_name = array(COLUMN => column), $c is UPPER CASED
			foreach ($this->aMetaColumns[$table] as $c => $ar)
			{
				$this->aMetaColumns[$table][$col_name[$c]] = $ar;
				unset($this->aMetaColumns[$table][$c]);
			}
			//print_r($this->aMetaColumns);
		}
		return $this->aMetaColumns[$table];
	} // end of func GetMetaColumns
	
	
	/**
	 * If current db is a mysql db.
	 * @return	boolean
	 */
	public function IsDbMysql() {
		return ('mysql' == substr($this->__conn->databaseType, 0, 5));
	} // end of func IsDbMysql
	
	
	/**
	 * If current db is a sybase db.
	 * @return	boolean
	 */
	public function IsDbSybase() {
		return ('sybase' == substr($this->aDbProfile['type'], 0, 6));
	} // end of func IsDbSybase
	
	
	/**
	 * Prepare and execute sql
	 * @param	string	$sql
	 * @param	array	$inputarr	Optional parameters in sql
	 * @return	object
	 */
	public function PExecute($sql, $inputarr = false)
	{
		$stmt = $this->Prepare($sql);
		return $this->Execute($stmt, $inputarr);
	} // end of PExecute
} // end of class Adodb
?>