<?php
/**
* @package      fwolflib
* @subpackage	mvc
* @copyright    Copyright 2008, Fwolf
* @author       Fwolf <fwolf.aide+fwolflib-mvc@gmail.com>
*/



/**
 * View in MVC
 * 
 * View是在Controler和Module之间起到一个融合的作用，它从Controler接受命令，从Module中接受数据，然后使用适当的模板和顺序来生成最终的html代码，然后交给Controler输出。
 * 
 * View主要体现为各项功能的page.php页面，相似的功能可以放在一个文件中进行处理，方便一些Module调用的共享。
 * 
 * View从Module得到结果数据后，使用Smarty模板进行加工，生成html，再交给Controler输出。
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
	 * Template object
	 * @var	object
	 */
	protected $oTpl = null;
	
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
	abstract protected function GenContent();
	abstract protected function GenFooter();
	
	
	/**
	 * construct
	 */
	public function __construct()
	{
		$this->CheckObjTpl();
		
		/* Template dir must be set before using
		$this->GenHeader();
		$this->GenMenu();
		$this->GenContent();
		$this->GenFooter();
		*/
	} // end of func __construct
	
	
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
	
} // end of class View
?>
