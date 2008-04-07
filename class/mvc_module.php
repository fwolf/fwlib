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
	 * Database object
	 * @var object
	 */
	protected $oDb = null;
	
	/**
	 * Call view object
	 * @var object
	 */
	public $oView = null;
	
	
	abstract protected function CheckObjDb();	// Check & init db object
	abstract protected function DbConn($s);		// Get db connection, because unknown db & dblib, implete it in application module class. Also can extend db connect class easily.
	
	
	/**
	 * construct
	 * @param object	&$view	Caller view object
	 */
	public function __construct(&$view)
	{
		$this->oView = $view;
	} // end of func __construct
	
	
} // end of class Module
?>
