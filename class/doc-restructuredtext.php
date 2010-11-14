<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright © 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-10-19
 */


require_once(dirname(__FILE__) . '/fwolflib.php');
require_once(FWOLFLIB . 'class/mvc-view.php');
require_once(FWOLFLIB . 'func/array.php');


/**
 * Display text document writing by reStructuredText markup language.
 *
 * Also include some other convert feature.
 *
 * Need jQuery, locate assigned in $aConfig['path_jquery']
 *
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright © 2010, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.class@gmail.com>
 * @since		2010-10-19
 */
class DocReStructuredText extends Fwolflib {

	/**
	 * Cmd options when used rst2xxx.py
	 *
	 * Param is combined in, eg: tab-width=4
	 * @var	array
	 */
	public $aCmdOption = array(
		'embed-stylesheet',
		//'link-stylesheet',

		// h1 is for title
		'initial-header-level=2',

		//'no-doc-title',
		//'no-xml-declaration',

		// Params why not support ?
		//'indents',
		//'newlines',
	);

	/**
	 * Result html
	 * @var	string
	 */
	public $sHtml = '';

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
		$this->InitConfig();
		$this->GetPathDocutils($path_docutils);

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
	 * Need: $this->aConfig['js_jquery'] = true;
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
	 * Gen magic comment including mode, coding
	 *
	 * @return	string
	 */
	public function GenRstMagicComment () {
		return "..	-*- mode: rst -*-\n"
			. "..	-*- coding: utf-8 -*-\n\n";
	} // end of func GenRstMagicComment


	/**
	 * Gen simple table by rst syntax
	 *
	 * Param $ar_thead[] format:
	 * array(
	 *	idx, index of data in $ar_data
	 *	title, text display in <th>, can be empty.
	 *	width, possible max length of text display in <td>
	 * )
	 * All text in th/td will be cut short if length > width.
	 * Columns' width will become td's width by their proportion,
	 * so it should make narrow col a bit wider.
	 *
	 * $ar_data is 2-dim array, index of 1st dim is ignored,
	 * index of 2st dim will be called by $ar_thead[][idx].
	 *
	 * @param	array	$ar_thead
	 * @param	array	$ar_data
	 * @return	string
	 */
	public function GenRstTable ($ar_thead, $ar_data) {
		// Split between column
		$s_split = str_repeat(' ', 4);
		//$s_split = "\t";

		// Table split line, total 3, 2 on/under th, 1 in table bottom line.
		$s_line = '';
		foreach ($ar_thead as $col) {
			$s_line .= str_repeat('=', $col['width']) . $s_split;
		}
		$s_line .= "\n";

		// Begin, th first
		$s_table = $s_line;
		foreach ($ar_thead as $col) {
			// Make them length = width
			$s = mb_strimwidth(ArrayRead($col, 'title', '')
					. str_repeat(' ', $col['width'])
				, 0, $col['width'], '', 'utf-8');
			$s_table .= $s . $s_split;
		}
		$s_table .= "\n" . $s_line;

		// Then, td
		foreach ($ar_data as $row) {
			foreach ($ar_thead as $col) {
				// Trim/fill length
				$s = mb_strimwidth(ArrayRead($row, $col['idx'], '')
						. str_repeat(' ', $col['width'])
					, 0, $col['width'], '', 'utf-8');
				$s_table .= $s . $s_split;
			}
			$s_table .= "\n";
		}

		// Table bottom
		$s_table .= $s_line;

		return $s_table;
	} // end of func GenRstTable


	/**
	 * Gen title by rst syntax
	 *
	 * @param	string	$s_title
	 * @return	string
	 */
	public function GenRstTitle ($s_title) {
		return str_repeat('=', 70) . "\n"
			. $s_title . "\n"
			. str_repeat('=', 70) . "\n\n";
	} // end of func GenRstTitle


	/**
	 * Get default path of docutils writer html4css1
	 *
	 * @return	string
	 */
	public function GetDocutilsCssPath () {
		$s_cmd = $this->GetPathRst2Html()
			. '--help |grep -A7 stylesheet-path=';
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
	 * Detect and set path of docutils
	 *
	 * @param	$s_path		Manual additional path
	 * @return	string
	 */
	public function GetPathDocutils ($s_path) {
		// Possible path of docutils execute file for choose
		$ar_path = array(
			'/usr/bin/',
			'/usr/local/bin/',
			'/bin/',
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
				$this->aConfig['cmd_.py'] = true;
				break;
			}
			// In some env like my (MT) Centos5, cmd hasn't .py extension
			if (is_executable($this->sPathDocutils . 'rst2html')) {
				$b_found = true;
				$this->aConfig['cmd_.py'] = false;
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
	} // end of func GetPathDocutils


	/**
	 * Got path of rst2html.py cmd
	 *
	 * @return	string
	 */
	public function GetPathRst2Html () {
		if ($this->aConfig['cmd_.py'])
			$s = $this->sPathDocutils . "rst2html.py ";
		else
			$s = $this->sPathDocutils . "rst2html ";
		return $s;
	} // end of func GetPathRst2Html


	/**
	 * Init config vars, give default value.
	 *
	 * @return	this
	 */
	public function InitConfig () {
		// Will set in GetPathDocutils()
		$this->aConfig['cmd_.py']	= true;
		// Use pipe to exec cmd instead of tmp file ?
		$this->aConfig['cmd_pipe']	= true;

		// Use script jquery ?
		$this->aConfig['js_jquery']	= false;

		// Tidy output html ?
		$this->aConfig['output_tidy'] = false;

		// Using 'Google AJAX Libraries API' now
		// http://code.google.com/apis/ajaxlibs/
		$this->aConfig['path_jquery']
			= 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js';
		//	= '/js/jquery.js';

		// Show opiton, default
		$this->aConfig['show_ads']		= false;
		$this->aConfig['show_counter']	= true;

		return $this;
	} // end of func InitConfig


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
	 * Note: I had run benchmark to compare pipe and tmpfile,
	 * 		result is, they are almost same.
	 *
	 * @param	string	$s_rst
	 * @return	string
	 */
	public function ToHtml ($s_rst) {
		$s_cmd = $this->GetPathRst2Html()
				. $this->GenCmdOption();
		$s_cmd = escapeshellcmd($s_cmd);

		if ($this->aConfig['cmd_pipe']) {
			// Use pipe
			$desc = array(
				0 => array('pipe', 'r'),
				1 => array('pipe', 'w'),
			);

			$proc = proc_open($s_cmd, $desc, $pipes);

			if (!is_resource($proc))
				$this->Log('Pipe can\'t open !', 5);
			else
				$this->Log("Tohtml using pipe by cmd: $s_cmd", 1);

			$fp = $pipes[0];
			fwrite($fp, $s_rst);
			fflush($fp);
			fclose($fp);

			$s_out = '';
			while (!feof($pipes[1])) {
				$s_out .= fgets($pipes[1]);
			}
			fclose($pipes[1]);

			proc_close($proc);
		}
		else {
			// Use tmp file
			$f_tmp = tempnam(sys_get_temp_dir(), 'fwolflib.doc-restructuredtext.');
			file_put_contents($f_tmp, $s_rst);

			// Execute cmd, got result.
			$s_cmd .= " $f_tmp";
			$this->Log("ToHtml by cmd: $s_cmd", 1);
			exec($s_cmd, $ar_out);

			unlink($f_tmp);
			$s_out = implode("\n", $ar_out);
		}

		$this->sHtml = $s_out;
		return $s_out;
	} // end of func ToHtml


	/**
	 * Convert reStructuredText content to html, and adjust
	 *
	 * @param	string	$s_rst
	 * @return	string
	 */
	public function ToHtmlFull ($s_rst) {
		$s_out = $this->ToHtml($s_rst);

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
	} // end of func ToHtmlFull


	/**
	 * Convert reStructuredText to html, only html body part.
	 *
	 * Without <body> tag.
	 *
	 * @param	string	$s_rst
	 * @return	string
	 */
	public function ToHtmlSimple ($s_rst) {
		$s_out = $this->ToHtml($s_rst);

		// Trim html before <body>
		$i = strpos($s_out, '<body>');
		if (!(false === $i))
			$s_out = substr($s_out, $i + 6);

		// Trim html after <body>
		$i = strrpos($s_out, '</body>');
		if (!(false === $i))
			$s_out = substr($s_out, 0, $i );

		$this->sHtml = $s_out;
		return $s_out;
	} // end of func ToHtmlSimple


} // end of class DocReStructuredText
?>
