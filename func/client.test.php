<?php
/**
 * Test - client function
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
require_once('fwolflib/func/client.php');

class TestFuncClient extends UnitTestCase {
	
    function TestClientIpFromToHex() {
    	// Default value
    	$this->assertEqual(ClientIpToHex(), '8302650a');
    	$this->assertEqual(ClientIpFromHex('8302650a'), '131.2.101.10');
    	// Loopback address
    	$this->assertEqual(ClientIpToHex('127.000.000.001'), '7f000001');
    	$this->assertEqual(ClientIpFromHex('7f000001'), '127.0.0.1');
    	// Mask address
    	$this->assertEqual(ClientIpToHex('255.255.255.255'), 'ffffffff');
    	$this->assertEqual(ClientIpFromHex('ffffffff'), '255.255.255.255');
    	// Normal address
    	$this->assertEqual(ClientIpToHex('202.99.160.68'), 'ca63a044');
    	$this->assertEqual(ClientIpFromHex('ca63a044'), '202.99.160.68');
    	// Error parameters handel
    	$this->assertEqual(ClientIpToHex('ABCD'), '');
    	$this->assertEqual(ClientIpFromHex('ABCD'), '');
    } // end of func 
    
} // end of class TestFuncString


// Change output charset in this way.
// {{{
$test = new TestFuncClient();
$test->run(new HtmlReporter('utf-8'));
// }}}
?>