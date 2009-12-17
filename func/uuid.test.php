<?php
/**
 * Test - uuid function
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
require_once('fwolflib/func/request.php');
require_once('fwolflib/func/uuid.php');

class TestFuncUuid extends UnitTestCase {

    function TestUuidParse() {
    	// Generate and parse data back
    	$ar = UuidParse(Uuid());
    	$this->assertIdentical($ar['custom_1'], '0000');
    	// Custom field
    	$ar = UuidParse(Uuid('0001'));
    	$this->assertIdentical($ar['custom_1'], '0001');
    	$ar = UuidParse(Uuid('0001', '1312.101'));
    	$this->assertIdentical($ar['custom_2'], '1312.101');
    	// Parae data
    	$ar = UuidParse('4822afd9-861b-0000-8302-650a25cda932');
    	$this->assertIdentical($ar['time_low'], 1210232793);
    	$this->assertIdentical($ar['time_mid'], 34331);
    	$this->assertIdentical($ar['custom_1'], '0000');
    	$this->assertIdentical($ar['custom_2'], '8302650a');
    	$this->assertIdentical($ar['ip'], '131.2.101.10');

		// Speed test
		UuidSpeedTest(5000);
    } // end of func

} // end of class TestFuncString


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('uuid.test.php' == $s_url) {
	$test = new TestFuncUuid();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
