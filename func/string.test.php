<?php
/**
 * Test - string func
 * @package     fwolflib
 * @subpackage	func-test
 * @copyright   Copyright 2004-2009, Fwolf
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

    function TestPin15To18() {
    	$x = '340524800101001';
    	$y = '34052419800101001X';
    	$this->assertEqual(Pin15To18($x), $y);

    	$x = '410881790605552';
    	$y = '410881197906055521';
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
