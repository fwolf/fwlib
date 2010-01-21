<?php
/**
 * Test - request function
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
require_once('fwolflib/func/request.php');

class TestFuncRequest extends UnitTestCase {

    function TestGetParam() {
		$_GET = array('a' => 1);
		$x = GetParam();
		$y = '?a=1';
		$this->assertEqual($x, $y);

		$_GET = array('a' => 1);
		$x = GetParam('b', 2);
		$y = '?a=1&b=2';
		$this->assertEqual($x, $y);

		$_GET = array('a' => 1, 'b' => '', 'c' => 3);
		$x = GetParam(array('a' => 2, 1 => 'a'), array('b', 'c'));
		$y = '?a=2&1=a';
		$this->assertEqual($x, $y);

		$_GET = array('a' => 1, 'b' => '', 'c' => 3);
		$x = GetParam(array('a' => 2, 1 => 'a'), 'b');
		$y = '?a=2&c=3&1=a';
		$this->assertEqual($x, $y);

    } // end of func TestGetParam


    function TestGetUrlPlan() {
    	$url = 'http://www.google.com/?a=https://something';
    	$this->assertEqual(GetUrlPlan($url), 'http');

    	$url = 'https://www.fwolf.com/';
    	$this->assertEqual(GetUrlPlan($url), 'https');

    	$url = 'ftp://domain.tld/';
    	$this->assertEqual(GetUrlPlan($url), 'ftp');

    	$url = '';
    	$this->assertPattern('/https?/i', GetUrlPlan($url));
    } // end of func TestGetUrlPlan

} // end of class TestFuncRequest


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('request.test.php' == $s_url) {
	$test = new TestFuncRequest();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
