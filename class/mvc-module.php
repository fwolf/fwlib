<?php
/**
* @package      fwolflib
* @subpackage	mvc
* @copyright    Copyright 2008, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
*/



/**
 * Module in MVC
 * 
 * 从View接受命令，完成数据的处理。只返回处理的结果数据，不对数据进行格式化。
 * 
 * Module主要体现为各对象的class.php文件，采用oop的思想来进行封装。
 * 
 * @package		fwolflib
 * @subpackage	mvc
 * @copyright	Copyright 2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
 * @since		2008-04-06
 * @version		$Id$
 * @see			Controler
 * @see			View
 */
abstract class Module {
	
	/**
	 * Number of items in list
	 * 
	 * In simple idea, this should set in view, 
	 * but pagesize is impleted as limit in select,
	 * so when generate sql you need to use it.
	 * @var int
	 */
	public $iPageSize = 10;
	
	/**
	 * Database object
	 * @var object
	 */
	protected $oDb = null;
	
	/**
	 * Call view object
	 * @var object
	 */
	public $oView = null;
	
	
	abstract protected function DbConn($dbprofile);		// Get db connection, because unknown db & dblib, implete it in application module class. Also can extend db connect class easily.
	
	
	/**
	 * construct
	 * @param object	&$view	Caller view object
	 */
	public function __construct(&$view)
	{
		$this->oView = $view;
	} // end of func __construct
	
	
	/**
	 * Check & init db object
	 * @param object	&$db			Db object
	 * @param array		$dbprofile	array(type,host,user,pass,name,lang)
	 * @return object
	 */
	protected function CheckObjDb(&$db, $dbprofile)
	{
		if (empty($db))
		{
			$db = $this->DbConn($dbprofile);
		}
		return $db;
	} // end of func CheckObjDb
	
	
} // end of class Module
?>