<?php
/**
 * Test - filesystem function
 *
 * @package     fwolflib
 * @subpackage	func.test
 * @copyright   Copyright 2010, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.func.test@gmail.com>
 * @since		2010-10-10
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
require_once(FWOLFLIB . 'func/filesystem.php');
require_once(FWOLFLIB . 'func/request.php');
require_once(FWOLFLIB . 'func/uuid.php');


class TestFuncFilesystem extends UnitTestCase {

    function TestGetFilenameToWrite () {
		// Prepare a filename
		$s_name = sys_get_temp_dir() . Uuid();
		$s_ext = 'ext';
		$s_file = $s_name . '.' . $s_ext;

		// Call 0
		$s = GetFilenameToWrite($s_file);
    	$this->assertEqual($s, $s_name . '.' . $s_ext);
		touch($s);

		// Call 1
		$s = GetFilenameToWrite($s_file);
    	$this->assertEqual($s, $s_name . '-1.' . $s_ext);
		mkdir($s);

		// Call 2
		$s = GetFilenameToWrite($s_file);
    	$this->assertEqual($s, $s_name . '-2.' . $s_ext);
		//touch($s);

    } // end of func GetFilenameToWrite


} // end of class TestFuncFilesystem


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if (__FILE__ == $s_url) {
	$test = new TestFuncFilesystem();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
