<?php
/**
* @package      fwolflib
* @subpackage	mvc
* @copyright    Copyright 2008, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
*/

require_once('fwolflib/func/request.php');
require_once('fwolflib/func/string.php');

/*
// In subclass or subclass for an app,
// the bottom layer class should define P2R first at here, and in this way:
if (!defined('P2R')) define('P2R', './');
// Then you can use P2R to require some app libs.
*/

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
abstract class Controler
{
	/**
	 * Run end time, used to caculate run time length
	 * @var	float
	 * @see $fTimeStart
	 */
	protected $fTimeEnd = 0;

	/**
	 * Run start time, used to caculate run time length
	 *
	 * Can only count to time when echo output.
	 * @var	float
	 * @see $fTimeEnd
	 */
	protected $fTimeStart = 0;

	/**
	 * View object
	 * @var	object
	 * @see ViewDisp()
	 */
	protected $oView = null;

	/**
	 * Action parameter, the view command to determin what to display
	 * @var string	// $_GET['a'], means which action user prefered of the module
	 */
	protected $sAction = '';

	/**
	 * Current module param
	 * @var	string	// $_GET['m'], means which module user prefered
	 */
	protected $sModule = '';


	abstract public function ViewErrorDisp($msg);	// Display view show error msg
	abstract public function Go();	// User call starter function


	/**
	 * contruct
	 */
	public function __construct()
	{
		// Record run start time first
		$this->fTimeStart = microtime(true);

		// Get major parameters
		$this->sModule = GetGet('m');
		$this->sAction = GetGet('a');

	} // end of func __construct


	/**
	 * Set run time length and db query times to View to display out.
	 *
	 * Need fwolflib::View,
	 * and use global var $i_db_query_times set in fwolflib::Adodb::CountDbQueryTimes
	 *
	 * Assign action is done in View::GenFooter.
	 *
	 * Cost about 0.05 more second when have db query.
	 *
	 * Eg: Processed in 0.054994 seconds, 6 db queries.
	 * @param	object	&$view	View object
	 * @global	int		$i_db_query_times
	 * @see	View::GenFooter
	 */
	public function SetInfoRuntime(&$view)
	{
		global $i_db_query_times;

		// Record run end time
		$this->fTimeEnd = microtime(true);
		// Generate info str, time used
		$s = '';
		$time_used = $this->fTimeEnd - $this->fTimeStart;
		$time_used = round($time_used, 4);
		$s .= "Processed in $time_used seconds";
		// Db query times
		if (isset($i_db_query_times))
			$s .= ", $i_db_query_times db queries";
		$s .= '.';

		// Assign msg to View's template object
		if (!is_null($view->oTpl))
			$view->oTpl->assign('debug_info_runtime', $s);
	} // end of func SetInfoRuntime


	/**
	 * Call a view class, display it's output
	 *
	 * Result echo out directly
	 * @param	string	$view	View define class file
	 * @param	string	$class	View class name, if obmit, will remove '_'&'-'
	 * 								in filename and use ucfirst($view) as class
	 * 								name.
	 * 							Auto remove beginning `v-` from $view is
	 * 							optional, if you use 'v-view.php' naming style,
	 * 							it will auto happen.
	 * @see	$oView
	 */
	protected function ViewDisp($view, $class = '')
	{
		// Check file existence
		if (file_exists($view))
			require_once($view);
		else
			$this->ViewErrorDisp("View define file $view not found!");

		// From ..../page_a.php, get 'page_a'.
		$s_view = substr($view, strrpos($view, '/') + 1);
		$s_view = substr($s_view, 0, strrpos($s_view, '.'));

		// Remove 'v-' from 'v-view.php', optional
		if ('v-' == substr($s_view, 0, 2))
			$s_view = substr($s_view, 2);

		// Then, 'page_a' to 'PageA'
		// Replace '-' in view name to '_'
		if (empty($class))
			$class = 'View' . StrUnderline2Ucfirst($s_view, true);

		if (class_exists($class))
		{
			$this->oView = new $class($this);
			//$p->oCtl = $this;	// Set caller object	// Moved to __contruct of View class, transfer $this when do new().

			echo $this->oView->GetOutput();
		}
		else
		{
			// Display error
			$this->ViewErrorDisp("View class $class not found!");
		}
	} // end of func ViewDisp


} // end of class Controler

?>
