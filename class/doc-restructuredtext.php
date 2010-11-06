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
		// Tidy output html ?
		'output_tidy'	=> false,

		// Using 'Google AJAX Libraries API' now
		// http://code.google.com/apis/ajaxlibs/
		'path_jquery'	=> 'http://ajax.googleapis.com/ajax/libs/jquery/1.4.3/jquery.min.js',
		//'path_jquery'	=> '/js/jquery.js',
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

		// Tidy ?
		if ($this->aConfig['output_tidy'])
			$s_out = $this->Tidy($s_out);

		return $s_out;
	} // end of func ToHtml


} // end of class DocReStructuredText
?>
