<?php
/**
 * Test - url func
 * @package     fwolflib
 * @subpackage	func.test
 * @copyright   Copyright 2009-2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func.test@gmail.com>
 * @since		2009-12-04
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
require_once('fwolflib/func/url.php');

class TestFuncUrl extends UnitTestCase {

    function TestUrlPlan() {
    	$url = 'http://www.google.com/?a=https://something';
    	$this->assertEqual(UrlPlan($url), 'http');

    	$url = 'https://www.fwolf.com/';
    	$this->assertEqual(UrlPlan($url), 'https');

    	$url = 'ftp://domain.tld/';
    	$this->assertEqual(UrlPlan($url), 'ftp');
    } // end of func TestUrlPlan

} // end of class TestFuncUrl


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('url.test.php' == $s_url) {
	$test = new TestFuncUrl();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
