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
require_once('fwolflib/class/list-table.php');
require_once('fwolflib/func/ecl.php');
require_once('fwolflib/func/request.php');
require_once('fwolflib/func/string.php');
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
		$s = '

{*
	Template of ListTable
*}

<div {if (0 < strlen($lt_id))} id="{$lt_id}_div" {/if}
	{if (0 < strlen($lt_class))} class="{$lt_class}" {/if}>

	{if (true == $lt_config.pager && true == $lt_config.pager_top)}
	<span id="{$lt_id}_pager_top" class="{$lt_id}_pager">
		{if (!empty($lt_url.p_first))}<a href="{$lt_url.p_first}">{$lt_config.pager_text_first}</a>{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_prev))}<a href="{$lt_url.p_prev}">{$lt_config.pager_text_prev}</a>{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_next))}<a href="{$lt_url.p_next}">{$lt_config.pager_text_next}</a>{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_last))}<a href="{$lt_url.p_last}">{$lt_config.pager_text_last}</a>{$lt_config.pager_text_spacer}{/if}
		{$lt_config.pager_text_cur_value}{$lt_config.pager_text_spacer}
		{$lt_config.pager_text_goto1}
		<form method="get" action="{$lt_url_form}">
			{$lt_url_form_hidden}
			<input type="text" name="{$lt_config.page_param}" value="{$lt_config.page_cur}" size="{if (99 < $lt_config.page_max)}<?php echo strlen(strval($lt_config.page_max)) - 1;?>{else}1{/if}" />
			{$lt_config.pager_text_goto2}
			<input type="submit" value="{$lt_config.pager_text_goto3}" />
		</form>
	</span>
	{/if}

	<table>
		<thead>
		<tr>
		{foreach from=$lt_title key=key item=title}
			<th>
				{if 1==$lt_config.orderby}
					<a href="{if $key==$lt_config.orderby_idx}{$lt_url.o_cur}{else}{$lt_url.o_other}{/if}&{$lt_config.orderby_param}_idx={$key}">
						{$title}{$key}
						{if $key==$lt_config.orderby_idx}
							{$lt_config.orderby_text}
						{/if}
					</a>
				{else}
					{$title}
				{/if}
			</th>
		{/foreach}
		</tr>
		</thead>

		<tbody>
		{foreach from=$lt_data item=row}
		<tr>
			{foreach from=$row item=col}
			<td>
				{$col}
			</td>
			{/foreach}
		</tr>
		{/foreach}
		</tbody>
	</table>

	{if (true == $lt_config.pager && true == $lt_config.pager_bottom)}
	<span id="{$lt_id}_pager_bottom" class="{$lt_id}_pager">
	{* Same with upper pager text *}
		{if (!empty($lt_url.p_first))}<a href="{$lt_url.p_first}">{$lt_config.pager_text_first}</a>{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_prev))}<a href="{$lt_url.p_prev}">{$lt_config.pager_text_prev}</a>{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_next))}<a href="{$lt_url.p_next}">{$lt_config.pager_text_next}</a>{$lt_config.pager_text_spacer}{/if}
		{if (!empty($lt_url.p_last))}<a href="{$lt_url.p_last}">{$lt_config.pager_text_last}</a>{$lt_config.pager_text_spacer}{/if}
		{$lt_config.pager_text_cur_value}{$lt_config.pager_text_spacer}
		{$lt_config.pager_text_goto1}
		<form method="get" action="{$lt_url_form}">
			{$lt_url_form_hidden}
			<input type="text" name="{$lt_config.page_param}" value="{$lt_config.page_cur}" size="{if (99 < $lt_config.page_max)}<?php echo strlen(strval($lt_config.page_max)) - 1;?>{else}1{/if}" />
			{$lt_config.pager_text_goto2}
			<input type="submit" value="{$lt_config.pager_text_goto3}" />
		</form>
	</span>
	{/if}

</div>


{*
	Coloring rows
	Using Id because when having multi-list, their id is different.
*}

{if (0 < strlen($lt_id))}
<script type="text/javascript">
<!--//--><![CDATA[//>
<!--
	// 把变色部分的style写入head，直接在body中写不符合规范
	$("head").append("\
		<style type=\"text/css\" media=\"screen, print\">\
		<!--\
		/*\
		// th用class不起作用，改成直接对styles属性赋值 2/2\
		.{$lt_id}_th {literal}{{/literal}\
			background-color: {$lt_config.color_bg_th};\
		{literal}}{/literal}\
		*/\
		.{$lt_id}_tr_even {literal}{{/literal}\
			background-color: {$lt_config.color_bg_tr_even};\
		{literal}}{/literal}\
		.{$lt_id}_tr_odd {literal}{{/literal}\
			background-color: {$lt_config.color_bg_tr_odd};\
		{literal}}{/literal}\
		/* 这个必须写在even/odd后面，不然不生效 */\
		.{$lt_id}_tr_hover {literal}{{/literal}\
			background-color: {$lt_config.color_bg_tr_hover};\
		{literal}}{/literal}\
		-->\
		</style>\
	");

/*
	旧的未使用class设置属性的方法，现在用class实现，能够更好的处理鼠标移入和移出的变色
	$("#{$lt_id} td").css("background-color", "{$lt_config.color_bg_th}");
	$("#{$lt_id} tr:even").css("background-color", "{$lt_config.color_bg_tr_even}");
	$("#{$lt_id} tr:odd").css("background-color", "{$lt_config.color_bg_tr_odd}");
*/
	// 设置行颜色、隔行变色
	// th用class不起作用，改成直接对styles属性赋值 1/2
	//$("#{$lt_id}_div th").addClass(".{$lt_id}_th");
	$("#{$lt_id}_div th").css("background-color", "{$lt_config.color_bg_th}");
	$("#{$lt_id}_div tbody tr:even").addClass("{$lt_id}_tr_even");
	//$("#{$lt_id}_div tbody tr:hover").addClass("{$lt_id}_tr_hover");
	$("#{$lt_id}_div tbody tr:odd").addClass("{$lt_id}_tr_odd");
	// When mouseover and mouseout, change color
	$("#{$lt_id}_div tbody tr").mouseover(function() {literal}{{/literal}
		$(this).addClass("{$lt_id}_tr_hover");
		{literal}}{/literal});
	$("#{$lt_id}_div tbody tr").mouseout(function() {literal}{{/literal}
		$(this).removeClass("{$lt_id}_tr_hover");
		{literal}}{/literal});

	// Pager\'s width is same with table, and position
	$(".{$lt_id}_pager").css("display", "block");
	$(".{$lt_id}_pager").css("text-align", "right");
	// Pager top leave a little margin-bottom to look better
	$("#{$lt_id}_pager_top").css("margin-bottom", "0.1em");
	if ($.browser.msie)
	{literal}{{/literal}
		$(".{$lt_id}_pager").css("width", $("#{$lt_id}_div table").attr("clientWidth"));
		// Same left margin with table
		$(".{$lt_id}_pager").css("margin-left"
			, ($("#{$lt_id}_div").attr("clientWidth")
			- $("#{$lt_id}_div table").attr("clientWidth")) / 2);
	{literal}}{/literal}
	else
	{literal}{{/literal}
		$(".{$lt_id}_pager").css("width"
			, $("#{$lt_id}_div table").css("width").replace("px", "") * 1);
		$(".{$lt_id}_pager").css("margin-left"
			, ($("#{$lt_id}_div").css("width").replace("px", "") * 1
			- $("#{$lt_id}_div table").css("width").replace("px", "") * 1) / 2);

	// Form vision
	$(".{$lt_id}_pager form").css("display", "inline");
	// Pager input auto select when click
	$(".{$lt_id}_pager form input").mouseover(function() {literal}{{/literal}
		this.select();
		{literal}}{/literal});
	{literal}}{/literal}

//--><!]]>
</script>
{/if}
';
		file_put_contents('/tmp/' . $this->sTpl, $s);
	} // end of func GenTpl


    function TestBasicTable() {
		$ar_conf = array(
			'tpl'		=> $this->sTpl,
			'page_size'	=> 3,
		);
		$this->oLt->SetConfig($ar_conf);

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
