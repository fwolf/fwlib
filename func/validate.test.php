<?php
/**
 * Test - validate func
 * @package     fwolflib
 * @subpackage	func.test
 * @copyright   Copyright 2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func.test@gmail.com>
 * @since		2010-12-13
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
require_once('fwolflib/func/validate.php');

class TestFuncValidate extends UnitTestCase {

	function TestValidateEmail() {
		$s = '87022964@163.com';
		$this->assertEqual(true, ValidateEmail($s));

		$s = 'fwolf.aide+fwolflib.func.test@gmail.com';
		$this->assertEqual(true, ValidateEmail($s));
	} // end of func TestMatchWildcard


} // end of class TestFuncValidate


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('validate.test.php' == $s_url) {
	$test = new TestFuncValidate();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
