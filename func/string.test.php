<?php
/**
 * Test - string func
 * @package     fwolflib
 * @subpackage	func-test
 * @copyright   Copyright 2004-2008, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib-func-test@gmail.com>
 * @since		2008-05-08
 * @version		$Id$
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
require_once('fwolflib/func/string.php');

class TestFuncString extends UnitTestCase {
	
    function TestRfc2047Decode() {
    	$x = 'Re: =?utf-8?B?5aiB5Y6/55Sz5oql5Yi25bqm?=';
    	$y = 'Re: 威县申报制度';
    	$this->assertEqual(Rfc2047Decode($x), $y);
    	
    	$x = '=?gbk?B?wLTX1HNqemxiekBzaW5hLmNvbbXE19S2r7vYuLQg?=';
    	$y = '来自sjzlbz@sina.com的自动回复 ';	// Without tailing ' ', will error.
    	$this->assertEqual(Rfc2047Decode($x), $y);
    	
    } // end of func 
    
} // end of class TestFuncString


// Change output charset in this way.
// {{{
$test = new TestFuncString();
$test->run(new HtmlReporter('utf-8'));
// }}}
?>
