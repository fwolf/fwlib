<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-10-19
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'class/mvc-view.php');


/**
 * Display text document writing by reStructuredText markup language.
 *
 * Also include some other convert feature.
 *
 * Need jQuery, locate assigned in $aConfig['path_jquery']
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-10-19
 */
class DocReStructuredText extends Fwolflib {

	/**
	 * Body text
	 *
	 * Will display out by sequence of their index if is array,
	 * or easy output if is string.
	 * @var mixed
	 */
	public $aBody = array();

	/**
	 * Cmd options when used rst2xxx.py
	 *
	 * Param is combined in, eg: tab-width=4
	 * @var	array
	 */
	public $aCmdOption = array(
		'embed-stylesheet',
		//'link-stylesheet',

		//'no-xml-declaration',

		// Params why not support ?
		//'indents',
		//'newlines',
	);

	/**
	 * Some config vars come from reference
	 * @var	array
	 */
	public $aConfig = array(
		// Use script jquery ?
		'js_jquery'	=> false,

		// Tidy output html ?
		'output_tidy'	=> false,

		// Using 'Google AJAX Libraries API' now
		// http://code.google.com/apis/ajaxlibs/
		'path_jquery'	=> 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js',
		//'path_jquery'	=> '/js/jquery.js',

		// Show opiton, default
		'show_ads'		=> false,
		'show_counter'	=> true,
	);

	/**
	 * Stored css styles, and their type
	 * @var array
	 */
	protected $aCss = array();

	/**
	 * Footer text
	 * @var array
	 */
	protected $aFooter = array();

	/**
	 * Header text
	 * @var array
	 */
	protected $aHeader = array();

	/**
	 * Result html
	 * @var	string
	 */
	public $sHtml = '';

	/**
	 * Document infomation
	 *
	 * Will display out by sequence of their index,
	 * Do NOT change there sequence.
	 * @var array
	 */
	public $aInfo = array(
		'title'			=> "Document title(aInfo['title'])",
		'author'		=> "Document author(aInfo['author'])",
		'authormail'	=> "Document author's mail(aInfo['authormail'])",
		'keywords'		=> "Keywords used in html <meta>(aInfo['keywords'])",
		'description'	=> "Description used in html <meta>(aInfo['description'])",

		// Above part can be set (aBody[]) in doc script.
		// Below 2 parts can be rewritten in __construct of subclass

		// Verinfo part
		'package'		=> "Phpdoc style package(aInfo['package'])",
		'subpackage'	=> "Phpdoc style subpackage(aInfo['subpackage'])",
		'copyright1'	=> "Copyright show in verinfo(aInfo['copyright1'])",
		'since'			=> "When is this file born(aInfo['since'])",
		'version'		=> "\$Id\$",
		// Footer part
		'copyright2'	=> array(
			"Copyright show in footer(aInfo['copyright2'])",
			"Will display in footer by list style.",
		),
	);

	/**
	 * Cmd line option
	 * @var	array
	 */
	public $aOption = array();

	/**
	 * Tidy output html ?
	 * @var	boolean
	 */
	public $bOutputTidy = false;

	/**
	 * Actural path of docutils execute file.
	 * @var	string
	 * @see	SetPathDocutils()
	 */
	protected $sPathDocutils = '';


	/**
	 * construct
	 *
	 * @var param	string	$path_docutils	Path of docutils exec file
	 */
	public function __construct ($path_docutils = '') {
		$this->SetPathDocutils($path_docutils);

		// Get docutils writer html4css1 path, and add to cmd param
		$this->aCmdOption[] = 'stylesheet-path="'
			. $this->GetDocutilsCssPath() . ','
			// Add my own css
			. dirname(__FILE__) . '/../css/doc-restructuredtext.css"';

		//$this->SetInfoFwolflib();
	} // end of class __construct


	/**
	 * Add my html footer
	 *
	 * Some code is cp from fwolfweb
	 *
	 * @param	string	$s_html
	 * @return	string
	 */
	public function AddFooter ($s_html) {
		$s = '';
		if ($this->aConfig['show_ads']) {
			$s .= '
				<div id="ads_bottom">
					<br />
					<script type="text/javascript">
					<!--//--><![CDATA[//>
					<!--
						google_ad_client = "pub-7916103707935920";
						google_ad_width = 728;
						google_ad_height = 90;
						google_ad_format = "728x90_as";
						google_ad_type = "text_image";
						//2006-10-28: independence_application
						google_ad_channel = "1115184146";
						google_color_border = "B4D0DC";
						google_color_bg = "FFFFFF";
						google_color_link = "0033FF";
						google_color_text = "6F6F6F";
						google_color_url = "008000";
					//--><!]]>
					</script>
					<script type="text/javascript"
					  src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
					</script>
				</div>
			';
		}

		$s .= '
			<div id="footer">
				<hr />
				<a class="img" href="http://validator.w3.org/check?uri=referer">
					<img src="http://www.fwolf.com/icon/xhtml-1.0.png" alt="Valid XHTML 1.0!" />
				</a>
				<span class="spacer" >&nbsp;</span>
				<a class="img" href="http://jigsaw.w3.org/css-validator/check/referer">
					<img src="http://www.fwolf.com/icon/css.png" alt="Valid CSS!" />
				</a>
		';

		if ($this->aConfig['show_counter']) {
			$s .= '
				<span class="spacer" >&nbsp;</span>
				<script type="text/javascript" src="http://js.users.51.la/272422.js"></script>
				<noscript>
					<div>
						<a href="http://www.51.la/?272422">
							<img alt="&#x6211;&#x8981;&#x5566;&#x514D;&#x8D39;&#x7EDF;&#x8BA1;" src="http://img.users.51.la/272422.asp" style="border:none" />
						</a>
					</div>
				</noscript>
			';
		}

		$s .= '
				<span id="copyright">
					Copyright &copy; 2005-2010 <a href="http://www.fwolf.com/">Fwolf</a>, All Rights Reserved.
				</span>
			</div>
		';

		$s_html = $this->AddToFooter($s_html, $s);
		return $s_html;
	} // end of func AddFooter


	/**
	 * Add jquery lib link in <head>
	 *
	 * @param	string	$s_html
	 * @return	string
	 */
	protected function AddJsJquery ($s_html) {
		$s_html = str_replace('</head>'
			, '<script type="text/javascript" src="'
					. $this->aConfig['path_jquery'] . '">'
				. '</script>'
				. "\n</head>"
			, $s_html);

		return $s_html;
	} // end of func AddJsJquery


	/**
	 * Add js code, which can show source of prefered part.
	 *
	 * @return	string
	 */
	public function AddJsShowSource () {
		$s = '
			<script type="text/javascript">
			<!--//--><![CDATA[//>
			<!--

			// At first, hide all source code
			$(".show_source").next("pre").css("display", "none");


			// Click to show/hide source code
			$(".show_source").click(function() {
				obj = $(this).next();
				if ("none" == obj.css("display")) {
					// Show it
					obj.show("slow");
				}
				else {
					// Hide it
					obj.hide("fast");
				}
			});

			//--><!]]>
			</script>
		';

		$this->sHtml = $this->AddToFooterBefore($this->sHtml, $s);
		return $this->sHtml;
	} // end of func AddJsShowSource


	/**
	 * Put log bottom at document
	 *
	 * @param	$level	Only output log which's level >= $level
	 * @return	string
	 */
	public function AddLog ($level = 3) {
		$s_log = parent::LogGet($level);
		$s_log = "<div class='log'>$s_log</div>";

		// Insert to document
		$this->sHtml = $this->AddToFooterBefore($this->sHtml, $s_log);

		return $this->sHtml;
	} // end of func AddLog


	/**
	 * Add some code to footer
	 *
	 * @param	string	$s_html
	 * @param	string	$s_add
	 * @return	stirng
	 */
	public function AddToFooter ($s_html, $s_add) {
		$i = strrpos($s_html, '</body>');
		if (! (false === $i)) {
			$s_html = substr($s_html, 0, $i) . $s_add
				. "\n</body>\n</html>";
		}

		return $s_html;
	} // end of func AddToFooter


	/**
	 * Add some code before footer div
	 *
	 * @param	string	$s_html
	 * @param	string	$s_add
	 * @return	stirng
	 */
	public function AddToFooterBefore ($s_html, $s_add) {
		$i = strrpos($s_html, '<div id="footer">');
		if (! (false === $i)) {
			$s_html = substr($s_html, 0, $i) . $s_add . "\n"
				. substr($s_html, $i);
		}

		return $s_html;
	} // end of func AddToFooterBefore


	/**
	 * Gen cmd options
	 *
	 * @return	string
	 */
	protected function GenCmdOption () {
		if (empty($this->aCmdOption)) {
			return '';
		}
		else {
			$s = ' ';
			foreach ($this->aCmdOption as $v) {
				// Single char param without '-'
				if (1 == strlen($v))
					$v = '-' . $v;
				// Multi char param without '--'
				if (1 < strlen($v) && '--' != substr($v, 0, 2))
					$v = '--' . $v;

				$s .= $v . ' ';
			}
			return $s;
		}
	} // end of func GenCmdOption


	/**
	 * Generate output html
	 *
	 * @return string
	 */
	public function GenOutputHtml() {
		$s = '';
		$s .= $this->GenOutputHtmlHeader();
		$s .= $this->GenOutputHtmlBody();
		$s .= $this->GenOutputHtmlFooter();
		return $s;
	} // end of func GenOutputHtml


	/**
	 * Get default path of docutils writer html4css1
	 *
	 * @return	string
	 */
	public function GetDocutilsCssPath () {
		$s_cmd = $this->sPathDocutils . 'rst2html.py '
			. '--help |grep -A6 stylesheet-path=';
		exec($s_cmd, $ar_out);
		$s_out = implode('', $ar_out);
		$s_out = str_replace('                        ', '', $s_out);

		// Find the css path
		if (0 < preg_match('/Default: \"(\S+?)\"/i', $s_out, $ar)) {
			$s_out = $ar[1];
			$this->Log("Got docutils css path: $s_out", 1);
		}
		else {
			$s_out = '';
			$this->Log('Can\'t find docutils css path.', 4);
		}

		return $s_out;
	} // end of func GetDocutilsCssPath


	/**
	 * Modify html doctype declare
	 *
	 * @param	string	$s_html
	 * @return	string
	 */
	protected function ModifyHtmlDoctype ($s_html) {
		$s_html = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
			, "<!DOCTYPE html\n	PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n	\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">"
			, $s_html);
		return $s_html;
	} // end of func ModifyHtmlDoctype


	/**
	 * Modify html <style> tag, to met validator.w3.org
	 *
	 * @param	string	$s_html
	 * @return	string
	 */
	protected function ModifyHtmlTagStyle ($s_html) {
		$s_html = str_replace('</style>'
			, "-->\n</style>", $s_html);
		$s_html = str_replace('<style type="text/css">'
			, "<style type=\"text/css\" media=\"screen, print\">\n<!--"
			, $s_html);
		return $s_html;
	} // end of func ModifyHtmlTagStyle


	/**
	 * Set info array
	 */
	public function SetInfoFwolflib() {
		$this->aInfo['package']		= "fwolflib";
		$this->aInfo['subpackage']	= "doc";
		$this->aInfo['copyright1']	= "Copyright &copy; 2009, Fwolf";
		$this->aInfo['since']		= "2009-04-19";
		$this->aInfo['version']		= "\$Id\$";
		$this->aInfo['copyright2']	= array(
			'Copyright &copy; 2009, Fwolf',
			'All Rights Reserved.',
		);
	} // end of func SetInfoFwolflib


	/**
	 * Detect and set path of docutils
	 *
	 * @param	$s_path		Manual additional path
	 * @return	string
	 */
	public function SetPathDocutils ($s_path) {
		// Possible path of docutils execute file for choose
		$ar_path = array(
			'/usr/bin/',
			'/usr/local/bin/',
		);

		if (!empty($s_path)) {
			// Add to array
			array_unshift($ar_path, $s_path);
		}

		// Find a usable path
		$b_found = false;
		while (!$b_found && !empty($ar_path)) {
			$this->sPathDocutils = $ar_path[0];
			if (is_executable($this->sPathDocutils . 'rst2html.py')) {
				$b_found = true;
				break;
			}
			array_shift($ar_path);
		}
		if ($b_found) {
			$this->Log('Got docutils execute file in '
				. $this->sPathDocutils, 1);
		}
		else {
			$this->sPathDocutils = '';
			$this->Log('Can\' find docutils execute file.', 5);
		}

		return $this->sPathDocutils;
	} // end of func SetPathDocutils


	/**
	 * Tidy output html
	 *
	 * @param	&$s_html
	 * @return	string
	 */
	protected function Tidy (&$s_html) {
		return View::Tidy($s_html);
	} // end of func Tidy


	/**
	 * Convert reStructuredText content to html format
	 *
	 * @param	string	$s_rst
	 * @return	string
	 */
	public function ToHtml ($s_rst) {
		$f_tmp = tempnam(sys_get_temp_dir(), 'fwolflib.doc-restructuredtext.');
		file_put_contents($f_tmp, $s_rst);

		// Execute cmd, got result.
		$s_cmd = $this->sPathDocutils . "rst2html.py "
			. $this->GenCmdOption() . " $f_tmp";
		$s_cmd = escapeshellcmd($s_cmd);
		$this->Log("ToHtml by cmd: $s_cmd", 1);
		exec($s_cmd, $ar_out);

		unlink($f_tmp);
		$s_out = implode("\n", $ar_out);

		// Fix html
		$s_out = $this->ModifyHtmlDoctype($s_out);
		$s_out = $this->ModifyHtmlTagStyle($s_out);

		// Need jQuery ?
		if ($this->aConfig['js_jquery'])
			$s_out = $this->AddJsJquery($s_out);

		// Add my footer
		$s_out = $this->AddFooter($s_out);

		// Tidy ?
		if ($this->aConfig['output_tidy'])
			$s_out = $this->Tidy($s_out);

		$this->sHtml = $s_out;
		return $s_out;
	} // end of func ToHtml


} // end of class DocReStructuredText
?>
