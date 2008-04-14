<?php
/**
* @package      fwolflib
* @subpackage	class
* @copyright    Copyright 2008, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib-class@gmail.com>
*/

// Set include path in __construct
//require_once('adodb/adodb.inc.php');

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
	} // end of class __construct
	
	
	/**
	 * Overload __call, redirect method call to adodb
	 * @var string	$name	Method name
	 * @var array	$arg	Method argument
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
			// :TODO: Will this proble solved if we drop default
			// database master from sa user ?
			if ('sybase' == substr($this->aDbProfile['type'], 0, 6))
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
			if ('mysql' == $this->__conn->databaseType)
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
	 * :TODO: UPDATE/INSERT/DELETE
	 * @param array	$ar_sql	Array(select=>..., from=>...)
	 * @return string
	 */
	public function GenSql($ar_sql)
	{
		$sql = '';
		
		if (is_array($ar_sql) && !empty($ar_sql))
			foreach ($ar_sql as $action => $param)
			{
				$action = strtoupper($action);
				switch ($action)
				{
					case 'SELECT':
						$sql .= ' SELECT ' . $this->GenSqlArrayAs($param, true, true);
						break;
					case 'FROM':
						// :NOTICE: 'FROM tbl as a', No space allowed in 'a'.
						$sql .= ' FROM ' . $this->GenSqlArrayAs($param, false, false);
						break;
					case 'WHERE':
						$sql .= ' WHERE ' . $this->GenSqlArray($param, ' AND ');
						break;
					case 'GROUPBY':
						$sql .= ' GROUP BY' . $this->GenSqlArray($param);
						break;
					case 'HAVING':
						$sql .= ' HAVING ' . $this->GenSqlArray($param, ' AND ');
						break;
					case 'ORDERBY':
						$sql .= ' ORDER BY ' . $this->GenSqlArray($param);
						break;
					case 'LIMIT':
						if ('sybase' != substr($this->aDbProfile['type'], 0, 6))
							$sql .= ' LIMIT ' . $this->GenSqlArray($param);
						break;
				}
			}
		
		return $sql;
	} // end of func GenSql
	
	
	/**
	 * Generate SQL part, which param is array and need to list out in plain format.
	 * 
	 * @param mixed	$param
	 * @param string	$s_split	String used between parts.
	 * @return string
	 */
	protected function GenSqlArray($param, $s_split = ', ')
	{
		$sql = '';
		if (is_array($param) && !empty($param))
			// Because of plain format, so $k is useless
			foreach ($param as $k=>$v)
			{
				/*
				if (is_int($k))
					$sql .= ", $v";
				else 
					$sql .= ", $k $v";
				*/
				$sql .= "$s_split $v";
			}
		else 
			$sql .= "$s_split $param";
		$sql = substr($sql, strlen($s_split));
		
		return $sql;
	} // end of func GenSqlArray
	
	
	/**
	 * Generate SQL part, which param is array and need use AS in it.
	 * @link http://dev.mysql.com/doc/refman/5.0/en/select.html
	 * @param mixed	$param	Items in SQL SELECT part, Array or string.
	 * 						Array($k=>$v) means '$k AS $v' in sql,
	 * 						but when $k is int, means '$v AS $v' in sql.
	 * @param boolean	$use_as	Sybase table alias can't use AS
	 * @param boolean	$quote	AS column alias, need to be quoted(true),
	 * 							AS table alias, need not to be quoted(false).
	 * @return string
	 */
	protected function GenSqlArrayAs($param, $use_as = true, $quote = false)
	{
		$sql = '';
		if (is_array($param) && !empty($param))
			foreach ($param as $k=>$v)
			{
				// If there are space in $v, it need to be quoted
				// so always quote it.
				if (is_int($k))
					$sql .= ", $v";
				else 
				{
					$s_split = ($quote) ? "'" : '';
					$s_as = ($use_as) ? 'AS' : '';
					$sql .= ", $k $s_as $s_split{$v}$s_split";
				}
			}
		else 
			$sql .= ", $param";
		$sql = substr($sql, 2);
		
		return $sql;
	} // end of func GenSqlArrayAs
	
	
} // end of class Adodb
?>