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

    function TestArrayAdd () {
		$ar = array();
		ArrayAdd($ar, 'a', 3);
		ArrayAdd($ar, 'a', 4);
		$this->assertEqual($ar['a'], 7);

		ArrayAdd($ar, 'b', 3);
		ArrayAdd($ar, 'b', '4');
		$this->assertEqual($ar['b'], '34');

    } // end of func TestArrayAdd


    function TestArrayEval () {
		$ar = array('a' => 'string');
		$s_eval = 'substr("{a}", 1, 2) == "tr"';
		$this->assertEqual(true, ArrayEval($s_eval, $ar));

		$s_eval = 'substr("string", 1, 2) == "tr"';
		$this->assertEqual(true, ArrayEval($s_eval));

		$s_eval = 'substr("{a}", 1, 2) == "tr"; return false;';
		$this->assertEqual(false, ArrayEval($s_eval, array()));
	} // end of func TestArrayEval


    function TestArrayInsert () {
		// Pos not exists, number indexed
		$ar_srce = array('a', 'b', 'c');
		$x = $ar_srce;
		$x = ArrayInsert($x, 'd', array('d'));
		$this->assertEqual(var_export($x, true)
			, var_export(array('a', 'b', 'c', 'd'), true));

		// Pos not exists, assoc indexed
		$ar_srce = array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
		);
		$x = $ar_srce;
		$x = ArrayInsert($x, 'd', array('d'));
		$this->assertEqual(var_export($x, true), var_export(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			0 => 'd',
		), true));

		// Assoc indexed, normal
		$ar_srce = array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'e' => 5,
		);
		$ar_ins = array(
			'ins1'	=> 'ins1',
			'ins2'	=> 'ins2',
		);
		// Insert before a key
		$x = $ar_srce;
		ArrayInsert($x, 'c', $ar_ins, -2);
		$this->assertEqual(var_export($x, true), var_export(array(
			'a' => 1,
			'ins1'	=> 'ins1',
			'ins2'	=> 'ins2',
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'e' => 5,
		), true));

		// Insert after a key
		$x = $ar_srce;
		ArrayInsert($x, 'c', $ar_ins, 2);
		$this->assertEqual(var_export($x, true), var_export(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'ins1'	=> 'ins1',
			'ins2'	=> 'ins2',
			'e' => 5,
		), true));

		// Replace
		$x = $ar_srce;
		ArrayInsert($x, 'a', $ar_ins, 0);
		$this->assertEqual(var_export($x, true), var_export(array(
			'ins1'	=> 'ins1',
			'ins2'	=> 'ins2',
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'e' => 5,
		), true));

		// Replace & not exist = append
		$x = $ar_srce;
		ArrayInsert($x, 'f', $ar_ins, 0);
		$this->assertEqual(var_export($x, true), var_export(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'e' => 5,
			'ins1'	=> 'ins1',
			'ins2'	=> 'ins2',
		), true));

		// Insert far before
		$x = $ar_srce;
		ArrayInsert($x, 'a', $ar_ins, -10);
		$this->assertEqual(var_export($x, true), var_export(array(
			'ins1'	=> 'ins1',
			'ins2'	=> 'ins2',
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'e' => 5,
		), true));

		// Insert far after
		$x = $ar_srce;
		ArrayInsert($x, 'e', $ar_ins, 10);
		$this->assertEqual(var_export($x, true), var_export(array(
			'a' => 1,
			'b' => 2,
			'c' => 3,
			'd' => 4,
			'e' => 5,
			'ins1'	=> 'ins1',
			'ins2'	=> 'ins2',
		), true));
	} // end of func TestTestArrayInsert


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
