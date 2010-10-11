<?php
/**
 * Test - array function
 * @package     fwolflib
 * @subpackage	func.test
 * @copyright   Copyright 2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func.test@gmail.com>
 * @since		2010-01-25
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
require_once(dirname(__FILE__) . '/../fwolflib.php');
require_once(FWOLFLIB . 'func/array.php');
require_once(FWOLFLIB . 'func/request.php');


class TestFuncArray extends UnitTestCase {

    function TestArrayRead() {
    	$ar = array('a' => 1);
    	$x = ArrayRead($ar, 'a', '2');
    	$this->assertEqual($x, 1);
    	$x = ArrayRead($ar, 'b', '2');
    	$this->assertEqual($x, 2);
    	$x = ArrayRead($ar, 3);
    	$this->assertEqual($x, null);
    } // end of func TestArrayRead


    function TestFilterWildcard() {
		$rule = 'a*, -*b, -??c, +?d*';
		$ar_srce = array(
			'ab',
			'abc',
			'adc',
		);
		$ar = FilterWildcard($ar_srce, $rule);
		$this->assertEqual($ar, array('adc'));
	} // end of func TestFilterWildcard


} // end of class TestFuncArray


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('array.test.php' == $s_url) {
	$test = new TestFuncArray();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
