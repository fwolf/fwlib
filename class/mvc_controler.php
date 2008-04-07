<?php
/**
* @package      fwolflib
* @subpackage	mvc
* @copyright    Copyright 2008, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
*/

require_once('fwolflib/func/request.php');

/**
 * Controler class in MVC
 *
 * 控制系统的哪些功能被调用，主要是对应主系统和子系统根下的index.php文件，这些文件始终是用户调用的文件，对于每个子系统也是这样。同理，全局常量P2R在Contoler中定义。
 * 
 * 主系统根下的index.php通过`\$_GET['m']`(module)来确定应该引用哪个子系统的index.php，相当于“进入某个子系统”。
 * 
 * 子系统根下的index.php通过`\$_GET['a']`(action)来调用哪个功能相应的Page，类似的功能可以是几个action都在一个页面中处理。
 * 
 * 主系统和子系统下的index.php要做到都可以被单独调用。
 * 
 * Controler还接收其它的`\$_GET`和`\$_POST`参数，并传递给View。
 * 
 * 如果子系统的功能比较多，还可以再设计一层Controler，用于实现功能的选择；也可以采用统一放入子系统的Controler中集中控制的方式。特殊情况下，也可以从主系统根直接调用功能Page。
 * 
 * 一般项目中，Controler只起到了用户命令的分流作用，处理较少，大量的页面生成、参数转换和传递都放到了View中。也正因为如此，如果启用了缓存机制，打算将缓存放在Controler中实现。
 * 
 * 这里所说的module和action主要是针对系统逻辑的，不要和MVC中的概念混淆。
 * 
 * @package		fwolflib
 * @subpackage	mvc
 * @copyright	Copyright 2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
 * @since		2008-04-06
 * @version		$Id$
 * @see			Module
 * @see			View
 */
abstract class Controler {
	
	/**
	 * Current module param
	 * @var	string	// $_GET['m'], means which module user prefered
	 */
	public $sModule = '';
	
	/**
	 * Current action param
	 * @var	string	// $_GET['a'], means which action user prefered of the module
	 */
	public $sAction = '';
	
	
	abstract public function Go();	// User call starter function
	
	
	/**
	 * contruct
	 */
	public function __construct()
	{
		// Get major parameters
		$this->sModule = GetGet('m');
		$this->sAction = GetGet('a');
		
		// Get other parameters
		$this->aGet = $this->ParseRequest($_GET);
		$this->aPost = $this->ParseRequest($_POST);
	} // end of func __construct
	
	
	/**
	 * Read $_REQUEST, write to class property
	 * @param	array	$request	// $_GET, $_POST, etc...
	 * @return	array
	 */
	protected function ParseRequest(&$request)
	{
		if (empty($request) or !is_array($request))
			return array();
		else 
		{
			$r = array();
			foreach ($request as $k => $v)
			{
				$r[$k] = GetRequest($request, $k);
			}
			return $r;
		}
	} // end of func ParseRequest
	
} // end of class Controler

?>