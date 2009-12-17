<?php
/**
 * @package		fwolflib
 * @subpackage	class.mvc
 * @copyright	Copyright 2008-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.mvc@gmail.com>
 * @since		2008-04-06
 * @version		$Id$
 */

require_once('fwolflib/class/form.php');
require_once('fwolflib/class/list-table.php');
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
 * If need to re-generate some part, you can directly call GenFooter() etc.
 * @package		fwolflib
 * @subpackage	class.mvc
 * @copyright	Copyright 2008-2009, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.mvc@gmail.com>
 * @since		2008-04-06
 * @version		$Id$
 * @see			Controler
 * @see			Module
 */
abstract class View {

	/**
	 * If use tidy to format output html code, default false.
	 * @var boolean
	 */
	public $bOutputTidy = false;

	/**
	 * View's caller -- Controler object
	 * @var	object
	 */
	public $oCtl = null;

	/**
	 * Form object
	 * @var	object
	 */
	public $oForm = null;

	/**
	 * ListTable object
	 * @var	object
	 */
	public $oLt = null;

	/**
	 * Template object
	 * @var	object
	 */
	public $oTpl = null;

	/**
	 * Action parameter, the view command to determin what to display
	 * @var string	// $_GET['a'], means which action user prefered of the module
	 */
	protected $sAction = null;

	/**
	 * Template file path
	 * @var	array
	 */
	protected $aTplFile = array(
		'footer' => 'footer.tpl',
		'header' => 'header.tpl',
		'menu' => 'menu.tpl',
		);

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

	/**
	 * Html <title> of this view
	 * @var	string
	 */
	protected $sViewTitle = '';


	abstract protected function CheckObjTpl();	// 检查、确定$oTpl已初始化

	/*
	// Changed to define directly in this class (below),
	//	sub class only need to set tpl file name or do some other action.
	abstract public function GenFooter();
	abstract public function GenHeader();
	abstract public function GenMenu();
	*/

	// An template is given, point to action-relate method,
	// and will check method exists at first.
	//abstract protected function GenContent();


	/**
	 * construct
	 * @param object	&$ctl	Caller controler object
	 */
	public function __construct(&$ctl)
	{
		$this->oCtl = $ctl;
		$this->sAction = GetGet('a');

		$this->CheckObjForm();
		$this->CheckObjTpl();
		$this->CheckObjLt();

		/* Template dir must be set before using
		$this->GenHeader();
		$this->GenMenu();
		$this->GenContent();
		$this->GenFooter();
		*/
	} // end of func __construct


	/**
	 * Check & init Form object
	 * @param	boolean	$forcenew
	 * @see	$oForm
	 */
	protected function CheckObjForm($forcenew = false) {
		if (empty($this->oForm) || $forcenew) {
			$this->oForm = new Form;
		}
		return $this->oForm;
	} // end of func CheckObjForm


	/**
	 * Check & init ListTable object
	 * @param	boolean	$forcenew
	 * @see	$oLt
	 */
	protected function CheckObjLt($forcenew = false)
	{
		if (empty($this->oLt) || $forcenew)
		{
			$this->oLt = new ListTable($this->oTpl);
		}
		return $this->oLt;
	} // end of func CheckObjLt


	/**
	 * Generate main content of page
	 *
	 * Doing this by call sub-method according to $sAction,
	 * Also, this can be override by extended class.
	 */
	public function GenContent()
	{
		if (empty($this->sAction))
			$this->oCtl->ViewErrorDisp("No action given.");

		// Check if action relate method existence, call it or report error.
		$s_func = 'GenContent' . StrUnderline2Ucfirst($this->sAction, true);
		if (method_exists($this, $s_func))
		{
			$this->sOutputContent = $this->$s_func();
			return $this->sOutputContent;
		}
		else
			// An invalid action is given
			$this->oCtl->ViewErrorDisp("The given action {$this->sAction} invalid or method $s_func doesn't exists.");
	} // end of func GenContent


	/**
	 * Generate footer part
	 */
	public function GenFooter()
	{
		// Set time used and db query executed time
		$this->oCtl->SetInfoRuntime($this);

		$this->sOutputFooter = $this->oTpl->fetch($this->aTplFile['footer']);
		return $this->sOutputFooter;
	} // end of func GenFooter


	/**
	 * Generate header part
	 */
	public function GenHeader()
	{
		$this->sOutputHeader = $this->oTpl->fetch($this->aTplFile['header']);
		return $this->sOutputHeader;
	} // end of func GenHeader


	/**
	 * Generate menu part
	 */
	public function GenMenu()
	{
		$this->sOutputMenu = $this->oTpl->fetch($this->aTplFile['menu']);
		return $this->sOutputMenu;
	} // end of func GenMenu


	/**
	 * Get content to output
	 * @see $sOutput
	 */
	public function GetOutput()
	{
		if (empty($this->sOutputHeader))
			$this->sOutputHeader = $this->GenHeader();
		if (empty($this->sOutputMenu))
			$this->sOutputMenu = $this->GenMenu();
		if (empty($this->sOutputContent))
			$this->sOutputContent = $this->GenContent();
		if (empty($this->sOutputFooter))
			$this->sOutputFooter = $this->GenFooter();
		$this->sOutput = $this->sOutputHeader .
						 $this->sOutputMenu .
						 $this->sOutputContent .
						 $this->sOutputFooter;

		// Use tidy ?
		if (true == $this->bOutputTidy)
			$this->sOutput = $this->Tidy($this->sOutput);

		return $this->sOutput;
	} // end of func GetOutput


	/**
	 * Set <title> of view page
	 * @param	string	$title
	 */
	public function SetViewTitle($title)
	{
		// Init tpl variables set
		$this->oTpl->assign_by_ref('view_title', $this->sViewTitle);

		$this->sViewTitle = $title;
		$this->sOutputHeader = $this->GenHeader();
	} // end of func SetViewTitle


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
