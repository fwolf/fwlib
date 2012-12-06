<?php
/**
 * Test - client function
 * @package		fwolflib
 * @subpackage	func.test
 * @copyright	Copyright 2012, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib.func.test@gmail.com>
 * @since		2012-12-06
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
require_once(dirname(__FILE__) . '/datetime.php');
require_once(dirname(__FILE__) . '/ecl.php');
require_once(dirname(__FILE__) . '/request.php');

class TestFuncDatetime extends UnitTestCase {

	function TestSecToStr () {
		$this->assertEqual(SecToStr(12), '12s');
		$this->assertEqual(SecToStr(120), '2i');

		$i = 65831316;
		$this->assertEqual(StrToSec(SecToStr($i, false)), $i);

		$i = 65831316985649;
		$this->assertEqual(StrToSec(SecToStr($i, false)), $i);
	} // end of func TestSecToStr


    function TestStrToSec () {
		$this->assertEqual(StrToSec(''), 0);
		$this->assertEqual(StrToSec(100), 100);
		$this->assertEqual(StrToSec('100'), 100);

		$this->assertEqual(StrToSec('3s'), 3);
		$this->assertEqual(StrToSec('2i 3s'), 123);
		$this->assertEqual(StrToSec('2I- 3s'), 117);
		$this->assertEqual(StrToSec('3I - 1i 3s'), 123);
		$this->assertEqual(StrToSec('2H- 118i -3s'), 117);
		$this->assertEqual(StrToSec('-118i2H-3s'), 117);

		$this->assertEqual(StrToSec('2centuries - 199Year-364DAY+ 4month
			-17w+2d-3d-24h1h-1hour+1h-58i2min-2minutes3s'), 123);
		$this->assertEqual(StrToSec('3s-2i2i-58i1h-1h1h-24h-3d2d
			-17w4m-364d-199y2c'), 123);
    } // end of func TestStrToSec

} // end of class TestFuncDatetime


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('datetime.test.php' == $s_url) {
	$test = new TestFuncDatetime();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
