<?php
/**
 * Test - ListTable class
 * @package     fwolflib
 * @subpackage	class-test
 * @copyright   Copyright 2009, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.class.test@gmail.com>
 * @since		2009-12-08
 */

// Define like this, so test can run both under eclipse and web alone.
// {{{
if (! defined('SIMPLE_TEST')) {
	define('SIMPLE_TEST', 'simpletest/');
	require_once(SIMPLE_TEST . 'autorun.php');
}
// Then set output encoding
//header('Content-Type: text/html; charset=utf-8');
// }}}

// Require library define file which need test
require_once('fwlib/class/list-table.php');
require_once('fwlib/func/ecl.php');
require_once('fwlib/func/request.php');
require_once('fwlib/func/string.php');
require_once('smarty/Smarty.class.php');

class TestListTable extends UnitTestCase {

	/**
	 * Table data, without title
	 * @var	array
	 */
	protected $aD = array();

	/**
	 * Table title
	 * @var	array
	 */
	protected $aT = array();

	/**
	 * ListTable instance
	 * @var	object
	 */
	protected $oLt = null;

	/**
	 * Css
	 * @var	string
	 */
	public $sCss = '';

	/**
	 * Js
	 * @var	string
	 */
	public $sJs = '';

	/**
	 * Tpl filename
	 * @var	string
	 */
	public $sTpl = 'list-table.test.tpl';


	/**
	 * Constructor
	 */
	public function __construct() {
		$o_tpl = new Smarty();
		// Configure dir
		$o_tpl->compile_dir = '/tmp/';
		$o_tpl->template_dir = '/tmp/';
		$o_tpl->cache_dir = '/tmp/';

		$this->oLt = new ListTable($o_tpl);

		$this->GenCss();
		$this->GenJs();
		$this->GenTbl();
		$this->GenTpl();

	} // end of func __construct


	/**
	 * Generate css for better table view
	 */
	protected function GenCss() {
		$this->sCss = '
<style type="text/css" media="screen, print">
<!--
div.fl_lt {
	border: 1px solid red;
	margin: auto;
	width: 70%;
}
/* Single line table */
table, td, th {
	border: 1px solid black;
	border-collapse: collapse;
	width: 100%;
	/* 等宽表格 */
	/*table-layout: fixed;*/
}
-->
</style>

<!-- This MUST before table code -->
<script type="text/javascript" src="/js/jquery.js">
</script>

';
	} // end of func GenCss


	/**
	 * Generate js for better table view
	 */
	protected function GenJs() {
		$this->sJs = '
<!-- This MUST be after table code -->
<script type="text/javascript">
<!--//--><![CDATA[//>
<!--

// Assign width for col n

// If "table-layout: fixed;" is assigned also,
// then td width is assigned + fixed_for_left,
// content width exceed limit will auto wrap,
// but overflow content can also been seen.

$(".fl_lt table").css("table-layout", "fixed");

$(".fl_lt tr > td:nth-child(2)").css("background-color", "green");
// * include th & td
$(".fl_lt tr > *:nth-child(1)").css("width", "12em");
//$(".fl_lt tr > td:nth-child(1)").css("width", "1em");

// If "table-layout: fixed;" is not assigned,
// width limit will work, but overflow content
// will make width raise.
$(".fl_lt-lt1 tr > *:nth-child(1)").css("width", "50%");

//--><!]]>
</script>

';
	} // end of func GenJs


	/**
	 * Generate random table title & data
	 */
	protected function GenTbl() {
		$i_col = 6;
		$i_row = 50;

		for ($i = 0; $i < $i_col; $i++) {
			$this->aT[$i] = RandomString(3, 'A');
		}

		for ($j = 0; $j < $i_row; $j++) {
			$this->aD[$j][0] = "$j - <strong>0</strong> - " . RandomString(20, 'a');
			for ($i = 1; $i < $i_col; $i++) {
				$this->aD[$j][$i] = "$j - $i";
			}
		}

	} // end of func GenTbl


	/**
	 * Generate tpl, this is also standard ListTable tpl
	 */
	protected function GenTpl() {
		$s = file_get_contents(FWOLFLIB . 'class/list-table.tpl');
		file_put_contents('/tmp/' . $this->sTpl, $s);
	} // end of func GenTpl


    function TestBasicTable() {
		$ar_conf = array(
			'tpl'		=> $this->sTpl,
			'page_size'	=> 3,
		);
		$this->oLt->SetCfg($ar_conf);

		$this->oLt->SetData($this->aD, $this->aT);
		//$this->oLt->SetId('');
		$this->oLt->SetPager();

		echo $this->sCss;
		echo($this->oLt->GetHtml());

		// Another table in same page
		$this->oLt->SetId('lt1');
		// Data is trimmed, need re-make
		$this->GenTbl();
		$this->oLt->SetData($this->aD, $this->aT);
		// Set sort
		$this->oLt->SetOrderby(0, 'asc');
		// MUST refresh pager
		$this->oLt->SetPager();
		echo($this->oLt->GetHtml());

		echo $this->sJs;

		Ecl('ListTable::GetSqlInfoFromUrl()');
		echo('<pre>'
			. var_export($this->oLt->GetSqlInfoFromUrl(), true)
			. '</pre>');

		Ecl('ListTable::GetSqlInfo()');
		echo('<pre>'
			. var_export($this->oLt->GetSqlInfo(), true)
			. '</pre>');
    } // end of func TestBasicTable

} // end of class TestListTable


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('list-table.test.php' == $s_url) {
	$test = new TestListTable();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
