<?php
/**
 * Test - string func
 * @package     fwolflib
 * @subpackage	func.test
 * @copyright   Copyright 2004-2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func.test@gmail.com>
 * @since		2008-05-08
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
require_once('fwolflib/func/ecl.php');
require_once('fwolflib/func/request.php');
require_once('fwolflib/func/string.php');

class TestFuncString extends UnitTestCase {

	function TestAddslashesRecursive () {
		$x = array(
			"It's 1.",
			"It's 2."	=> "It's 3.",
			2012,
			"It's 4."	=> array(
				"It's 5."	=> array(
					"It's 6."	=> "It's 7.",
				),
			'end',
			),
		);
		$y = array(
			"It\\'s 1.",
			"It\\'s 2."	=> "It\\'s 3.",
			2012,
			"It\\'s 4."	=> array(
				"It\\'s 5."	=> array(
					"It\\'s 6."	=> "It\\'s 7.",
				),
			"end",
			),
		);
		$this->assertEqual($y, AddslashesRecursive($x));
	} // end of func TestAddslashesRecursive


	function TestJsonEncodeHex () {
		$x = true;
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

		$x = 3.86;
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

		$x = array('a'=>1,'b'=>2,'c'=>3,'d'=>4,'e'=>5);
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

		$x = array(5 => 'a',4 => 'b', 3 => 'c');
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

		$x = array(1 => 'a',2 => 'b', 3 => 'c');
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

		$x = array('<foo>',"'bar'",'"baz"','&blong&', true);
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

		$x = array('<foo>' => "'bar'",'"baz"' => '&blong&', 3.86);
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

		$x = array('<foo>' => "'bar'",'"baz"' => '&blong&', 2 => 'a我d');
		$y = JsonEncodeHex($x);
		$this->assertEqual($x, json_decode($y, true));

	} // end of func TestJsonEncodeHex


	function TestMatchWildcard() {
		$s = 'abcdefg';
		$this->assertEqual(true, MatchWildcard($s, 'a*e?g'));
		$this->assertEqual(true, MatchWildcard($s, '?b*e*'));
		$this->assertEqual(false, MatchWildcard($s, '?b*e?'));
		$s = 'abc';
		$this->assertEqual(true, MatchWildcard($s, 'a*'));
	} // end of func TestMatchWildcard


    function TestOrgCodeGen () {
		$x = 'D2143569';
		$y = 'D2143569-X';
		$this->assertEqual(OrgCodeGen($x), $y);

		$x = 'd2143569';
		$y = 'D2143569-X';
		$this->assertEqual(OrgCodeGen($x), $y);

		$y = OrgCodeGen($x);
		$x = substr($x, 0, 8);
		$this->assertEqual(OrgCodeGen($x), $y);

		$x = 'D214356';
		$y = '';
		$this->assertEqual(OrgCodeGen($x), $y);

		$x = 'D214356-';
		$y = '';
		$this->assertEqual(OrgCodeGen($x), $y);

    } // end of func TestOrgCodeGen


    function TestPin15To18() {
		$x = '340524800101001';
		$y = '34052419800101001X';
		$this->assertEqual(Pin15To18($x), $y);

		$x = '410881790605552';
		$y = '410881197906055527';
		$this->assertEqual(Pin15To18($x), $y);

    } // end of func TestPin15To18


    function TestRfc2047Decode() {
    	$x = 'Re: =?utf-8?B?5aiB5Y6/55Sz5oql5Yi25bqm?=';
    	$y = 'Re: 威县申报制度';
    	$this->assertEqual(Rfc2047Decode($x), $y);

    	$x = '=?gbk?B?wLTX1HNqemxiekBzaW5hLmNvbbXE19S2r7vYuLQg?=';
    	$y = '来自sjzlbz@sina.com的自动回复 ';	// Without tailing ' ', will error.
    	$this->assertEqual(Rfc2047Decode($x), $y);

    } // end of func TestRfc2047Decode


    function TestSubstrIgnHtml() {
    	$x = '测试12&lt;4测试';
    	$x = SubstrIgnHtml($x, 11, '...');
		$y = '测试12&lt;4...';
    	$this->assertEqual($x, $y);

    	$x = '测<b><i><br / >试</i></b>&quot;<b>234测试</b>';
    	$x = SubstrIgnHtml($x, 9, '...');
		$y = '测<b><i><br / >试</i></b>&quot;<b>2...</b>';
    	$this->assertEqual($x, $y);

		$x = '`reStructuredText 中文示例 <?f=20101113-restructuredtext-example.rst>`_';
		$y = SubstrIgnHtml($x, 71, '');
		$this->assertEqual($x, $y);

    } // end of func TestSubstrIgnHtml


} // end of class TestFuncString


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('string.test.php' == $s_url) {
	$test = new TestFuncString();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
