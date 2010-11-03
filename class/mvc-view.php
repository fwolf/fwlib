<?php
/**
 * @package		fwolflib
 * @subpackage	class.mvc
 * @copyright	Copyright 2008-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.mvc@gmail.com>
 * @since		2008-04-06
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'class/cache.php');
require_once(FWOLFLIB . 'class/form.php');
require_once(FWOLFLIB . 'class/list-table.php');
require_once(FWOLFLIB . 'func/string.php');
require_once(FWOLFLIB . 'func/request.php');


/**
 * View in MVC
 *
 * View是在Controler和Module之间起到一个融合的作用，它从Controler接受命令，
 * 从Module中接受数据，然后使用适当的模板和顺序来生成最终的html代码，
 * 然后交给Controler输出。
 *
 * View主要体现为各项功能的page.php页面，相似的功能可以放在一个文件中进行处理，
 * 方便一些Module调用的共享。
 *
 * View从Module得到结果数据后，使用Smarty模板进行加工，生成html，再交给Controler输出。
 *
 * Action的处理主要在View中，Action的默认值也在View中赋予和实现。
 *
 *
 * Output generate sequence:
 * GetOutput()
 * 	GenHeader()
 * 	GenMenu()
 * 	GenContent()
 * 		Will auto call GenXxx() or GenContentXxx() is exists.
 * 	GenFooter()
 *
 *
 * If need to re-generate some part, you can directly call GenFooter() etc.
 *
 * Apply 'cache=0' at end of url will force cache update,
 * notice there is no cache stored for url plused 'cache=0'.
 *
 *
 * Roadmap:
 *
 * 2010-06-21	1.1
 * 		Rename GenContentXxx() to GenXxx(), with backward compative.
 * 2010-05-21	1.0	60d16e2417
 * 		Basic feature.
 *
 *
 * @package		fwolflib
 * @subpackage	class.mvc
 * @copyright	Copyright 2008-2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class.mvc@gmail.com>
 * @since		2008-04-06
 * @see			Controler
 * @see			Module
 */
abstract class View extends Cache {

	/**
	 * Action parameter, the view command to determin what to display
	 * @var string	// $_GET['a'], means which action user prefered of the module
	 */
	protected $sAction = null;

	/**
	 * If cache turned on
	 * Remember to set cache config before turned it on.
	 * @var	boolean
	 */
	public $bCacheOn = false;

	/**
	 * Css file url used in header
	 * eg: 'default.css', 'screen, print', indexed by 0,1
	 * @var	array
	 */
	public $aCss = array();

	/**
	 * View's caller -- Controler object
	 * @var	object
	 */
	public $oCtl = null;

	/**
	 * Form object, auto new when first used.
	 * @var	object
	 */
	public $oForm = null;

	/**
	 * ListTable object, auto new when first used.
	 * @var	object
	 */
	public $oLt = null;

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
	 * If use tidy to format output html code, default false.
	 * @var boolean
	 */
	public $bOutputTidy = false;

	/**
	 * If show debug info on footer ?
	 * @var	boolean
	 */
	public $bShowDebugInfo = false;

	/**
	 * Template object, auto new when first used.
	 * @var	object
	 */
	public $oTpl = null;

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
	 * Html <title> of this view
	 * @var	string
	 */
	protected $sViewTitle = '';


	// New Tpl object
	abstract protected function NewObjTpl();


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
	public function __construct (&$ctl) {
		// For auto-new
		unset($this->oForm);
		unset($this->oLt);
		unset($this->oTpl);

		$this->oCtl = $ctl;
		$this->sAction = GetGet('a');

/*
		$this->NewObjForm();
		$this->NewObjTpl();
		$this->NewObjLt();
*/

		/* Template dir must be set before using
		$this->GenHeader();
		$this->GenMenu();
		$this->GenContent();
		$this->GenFooter();
		*/
	} // end of func __construct


	/**
	 * Auto new obj if not set, for some special var only
	 *
	 * @param	string	$name
	 * @return	object
	 */
	public function __get($name)
	{
		if ('o' == $name{0}) {
			$s_func = 'NewObj' . substr($name, 1);
			if (method_exists($this, $s_func)) {
				// New object
				$this->$name = $this->$s_func();
				return $this->$name;
			}
		}

		return null;
	} // end of func __get


	/**
	 * Gen key of cache
	 *
	 * @return	string
	 */
	public function CacheGenKey() {
		$key = $_SERVER['REQUEST_URI'];
		$key = str_replace(array('?', '&', '=', '//'), '/', $key);

		// Remove '/' at beginning of url
		if (0 < strlen($key) && '/' == $key{0})
			$key = substr($key, 1);

		// When force update cache, ignore 'cache=0' in url
		if ('0' == GetGet('cache')) {
			// Can't unset($_GET['cache']);
			// Because CacheNeedUpdate() need to check later

			$key = str_replace('/cache/0', '', $key);
		}

		return $key;
	} // end of func CacheGenKey


	/**
	 * Cache gen, directly use output html.
	 *
	 * @param	string	$key
	 * @return	string
	 */
	protected function CacheGenVal($key) {
		return $this->GetOutput();
	} // end of func CacheGenVal


	/**
	 * Get content to output with cache
	 *
	 * @return	string
	 */
	public function CacheGetOutput() {
		$key = $this->CacheGenKey();
		return $this->CacheLoad($key, 3);
	} // end of func CacheGetOutput


	/**
	 * Got cache lifetime, by second
	 * Should often re-define in sub class.
	 *
	 * @param	string	$key
	 * @return	int
	 */
	public function CacheLifetime($key) {
		return 60 * 60;
	} // end of func CacheLifetime


	/**
	 * Is cache data file need update/create ?
	 *
	 * @param	string	$key
	 * @return	boolean
	 */
	protected function CacheNeedUpdate($key) {
		if ('0' == GetGet('cache')) {
			return true;
		} else
			return parent::CacheNeedUpdate($key);
	} // end of func CacheNeedUpdate


	/**
	 * Write data to cache file, raw string
	 *
	 * @param	string	$key
	 * @param	mixed	$val
	 */
	public function CacheWrite($key, $val) {
		$s_file = $this->CachePath($key);
		$s_cache = &$val;

		// Create each level dir if not exists
		$s_dir = DirName1($s_file);
		if (!file_exists($s_dir))
			mkdir($s_dir, 0755, true);

		// Finally write file
		file_put_contents($s_file, $s_cache, LOCK_EX);
	} // end of func CacheWrite


	/**
	 * Generate main content of page
	 *
	 * Doing this by call sub-method according to $sAction,
	 * Also, this can be override by extended class.
	 */
	public function GenContent() {
		if ('content' == strtolower($this->sAction))
			$this->oCtl->ViewErrorDisp("Action shoud not named 'content'.");

		if (empty($this->sAction))
			$this->oCtl->ViewErrorDisp("No action given.");

		// Check if action relate method existence, call it or report error.
		$s = StrUnderline2Ucfirst($this->sAction, true);
		$s_func = 'Gen' . $s;
		$s_func1 = 'GenContent' . $s;
		if (method_exists($this, $s_func)) {
			$this->sOutputContent = $this->$s_func();
			return $this->sOutputContent;
		}
		elseif (method_exists($this, $s_func1)) {
				$this->sOutputContent = $this->$s_func1();
				return $this->sOutputContent;
		}
		else
			// An invalid action is given
			$this->oCtl->ViewErrorDisp("The given action {$this->sAction} invalid or method $s_func doesn't exists.");
	} // end of func GenContent


	/**
	 * Generate footer part
	 */
	public function GenFooter() {
		$this->sOutputFooter = $this->oTpl->fetch($this->aTplFile['footer']);

		// Set time used and db query executed time
		if ($this->bShowDebugInfo)
			$this->sOutputFooter = str_replace('<!-- debug info -->'
				, $this->oCtl->GetDebugInfo($this)
				. '<!-- debug info -->'
				, $this->sOutputFooter);

		return $this->sOutputFooter;
	} // end of func GenFooter


	/**
	 * Generate header part
	 */
	public function GenHeader () {
		$this->oTpl->assign_by_ref('css', $this->aCss);
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
	 *
	 * @return string
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
	 * New Form object
	 *
	 * @see	$oForm
	 */
	protected function NewObjForm() {
		return new Form;
	} // end of func NewObjForm


	/**
	 * New ListTable object
	 *
	 * @see	$oLt
	 */
	protected function NewObjLt() {
		return new ListTable($this->oTpl);
	} // end of func NewObjLt


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
		if (true == class_exists("tidy")) {
			// Specify configuration
			$config = array(
				'doctype'		=> 'strict',
				'indent'		=> true,
				'indent-spaces'	=> 2,
				'output-xhtml'	=> true,
				'wrap'			=> 200
			);
			// Do tidy
			$tidy = new tidy;
			$tidy->parseString($html, $config, 'utf8');
			$tidy->cleanRepair();

			return tidy_get_output($tidy);
		} else
			return $html;
	} // end of func Tidy

} // end of class View
?>
