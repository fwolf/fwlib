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
 * 执行返回的数据还是需要转码的，不过返回数据的种类太多，放在应用中实现更简单一些，这里不作。
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
	 * @param $forceNew			force new connection
	 * @return boolean
	 */
	public function Connect($forceNew = false)
	{
		try
		{
			$rs = $this->__conn->Connect($this->aDbProfile['host'], 
										 $this->aDbProfile['user'], 
										 $this->aDbProfile['pass'], 
										 $this->aDbProfile['name'], 
										 $forceNew);
			
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
	
	
} // end of class Adodb
?>