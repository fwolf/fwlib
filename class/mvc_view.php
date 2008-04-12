<?php
/**
* @package      fwolflib
* @subpackage	mvc
* @copyright    Copyright 2008, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
*/

require_once('fwolflib/func/string.php');
require_once('fwolflib/func/request.php');

/**
 * View in MVC
 * 
 * View是在Controler和Module之间起到一个融合的作用，它从Controler接受命令，从Module中接受数据，然后使用适当的模板和顺序来生成最终的html代码，然后交给Controler输出。
 * 
 * View主要体现为各项功能的page.php页面，相似的功能可以放在一个文件中进行处理，方便一些Module调用的共享。
 * 
 * View从Module得到结果数据后，使用Smarty模板进行加工，生成html，再交给Controler输出。
 * 
 * Action的处理主要在View中，Action的默认值也在View中赋予和实现。
 * 
 * @package		fwolflib
 * @subpackage	mvc
 * @copyright	Copyright 2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
 * @since		2008-04-06
 * @version		$Id$
 * @see			Controler
 * @see			Module
 */
abstract class View {
	
	/**
	 * View's caller -- Controler object
	 * @var	object
	 */
	public $oCtl = null;
	
	/**
	 * Template object
	 * @var	object
	 */
	protected $oTpl = null;
	
	/**
	 * Action parameter, the view command to determin what to display
	 * @var string	// $_GET['a'], means which action user prefered of the module
	 */
	protected $sAction = null;
	
	/**
	 * Output content generated
	 * @var	string
	 */
	public $sOutput = '';
	
	/**
	 * Main content part of output content, normail is page main content
	 * @var	string
	 */
	protected $sOutputContent = '';
	
	/**
	 * Footer part of output content
	 * 
	 * In common, this will include some end part of <body> and etc.
	 * @var string
	 */
	protected $sOutputFooter = '';
	
	/**
	 * Header part of output content, normally is html header part
	 * 
	 * In common, this will include all <html> and some beginner part of <body>
	 * @var	string
	 */
	protected $sOutputHeader = '';
	
	/**
	 * Menu part of output content, optional
	 * @var	string
	 */
	protected $sOutputMenu = '';
	
	
	abstract protected function CheckObjTpl();	// 检查、确定$oTpl已初始化
	abstract protected function GenHeader();
	abstract protected function GenMenu();
	// An template is given, point to action-relate method,
	// and will check method exists at first.
	//abstract protected function GenContent();
	abstract protected function GenFooter();
	
	
	/**
	 * construct
	 * @param object	&$ctl	Caller controler object
	 */
	public function __construct(&$ctl)
	{
		$this->oCtl = $ctl;
		$this->sAction = GetGet('a');
		
		$this->CheckObjTpl();
		
		/* Template dir must be set before using
		$this->GenHeader();
		$this->GenMenu();
		$this->GenContent();
		$this->GenFooter();
		*/
	} // end of func __construct
	
	
	/**
	 * Generate main content of page
	 * 
	 * Doing this by call sub-method according to $sAction,
	 * Also, this can be override by extended class.
	 */
	protected function GenContent()
	{
		if (empty($this->sAction))
			$this->oCtl->DispError("No action given.");
		
		// Check if action relate method existence, call it or report error.
		$s_func = 'GenContent' . StrUnderline2Ucfirst($this->sAction);
		if (method_exists($this, $s_func))
			$this->sOutputContent = $this->$s_func();
		else 
			// An invalid action is given
			$this->oCtl->DispError("The given action {$this->sAction} invalid or method $s_func doesn't exists.");
	} // end of function GenContent
	
	
	/**
	 * Get content to output
	 * @see $sOutput
	 */
	public function GetOutput()
	{
		if (empty($this->sOutputHeader))
			$this->GenHeader();
		if (empty($this->sOutputMenu))
			$this->GenMenu();
		if (empty($this->sOutputContent))
			$this->GenContent();
		if (empty($this->sOutputFooter))
			$this->GenFooter();
		$this->sOutput = $this->sOutputHeader . 
						 $this->sOutputMenu . 
						 $this->sOutputContent . 
						 $this->sOutputFooter;
		return $this->sOutput;
	} // end of func GetOutput
	
	
	/**
	 * Use tidy to format html string
	 * @param string	&$html
	 * @return string
	 */
	public function Tidy(&$html)
	{
		// Specify configuration
		$config = array(
		           'indent'         => true,
		           'indent-spaces'	=> 4,
		           'output-xhtml'   => true,
		           'wrap'           => 200);
		// Do tidy
		$tidy = new tidy;
		$tidy->parseString($html, $config, 'utf8');
		$tidy->cleanRepair();
		
		return $tidy;
	} // end of func Tidy
	
} // end of class View
?>
