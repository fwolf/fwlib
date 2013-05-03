<?php
/**
 * Test - Return value class
 *
 * @package     fwolflib
 * @subpackage	class.test
 * @copyright   Copyright 2013, Fwolf
 * @author      Fwolf <fwolf.aide+fwolflib.class.test.cache@gmail.com>
 * @since		2013-05-03
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
require_once(FWOLFLIB . 'class/rv/rv.php');
require_once(FWOLFLIB . 'func/ecl.php');
require_once(FWOLFLIB . 'func/request.php');
require_once(FWOLFLIB . 'func/string.php');

class TestRv extends UnitTestCase {

	/**
	 * Constructor
	 */
	public function __construct () {

	} // end of func __construct


	public function TestRvDefault () {
		$rv = new Rv();

		$this->assertEqual(0, $rv->Code());
		$this->assertEqual('hi', $rv->Msg('hi'));
		$this->assertEqual('hi', $rv->Msg(null));
		$this->assertEqual(null, $rv->Msg(null, true));

		$rv->Code(3);
		$this->assertEqual(false, $rv->Error());
		$rv->Code(-3);
		$this->assertEqual(true, $rv->Error());

	} // end of func TestRvDefault


} // end of class TestRv


// Change output charset in this way.
// {{{
$s_url = GetSelfUrl(false);
$s_url = substr($s_url, strrpos($s_url, '/') + 1);
if ('rv.test.php' == $s_url) {
	$test = new TestRv();
	$test->run(new HtmlReporter('utf-8'));
}
// }}}
?>
